'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import uuid from "react-uuid"
import PanelApp from "../Panel/PanelApp"
import {fetchJson} from "../component/fetchJson"
import {createPassword} from "../component/createPassword"
import {
    setPanelErrors,
    trans,
    downloadFile,
    openUrl,
    buildState,
    mergeParentForm,
    getParentFormName,
    getParentForm,
    deleteFormElement,
    changeFormValue,
    replaceName,
    replaceFormElement,
    findElementById,
    buildFormData,
    isSubmit,
    checkHiddenRows
} from "../Container/ContainerFunctions"

export default class LibraryApp extends Component {
    constructor (props) {
        super(props)
        this.panels = props.panels ? props.panels : {}
        this.content = props.content ? props.content : null
        this.translations = props.translations
        this.actionRoute = props.actionRoute

        if (Object.keys(this.panels).length === 0 && this.content !== null) {
            this.panels['default'] = {}
            this.panels.default['name'] = 'default'
            this.panels.default['disabled'] = true
            this.panels.default['content'] = this.content
        }

        this.functions = {
            translate: this.translate.bind(this),
            openUrl: openUrl.bind(this),
            downloadFile: downloadFile.bind(this),
            onSelectTab: this.onSelectTab.bind(this),
            deleteFile: this.deleteFile.bind(this),
            submitForm: this.submitForm.bind(this),
            onElementChange: this.onElementChange.bind(this),
            deleteElement: this.deleteElement.bind(this),
            addElement: this.addElement.bind(this),
            onCKEditorChange: this.onCKEditorChange.bind(this),
            generateNewPassword: this.generateNewPassword.bind(this),
            selectLibraryAndType: this.selectLibraryAndType.bind(this),
            loadGoogleBookData: this.loadGoogleBookData.bind(this),
            renderImageLocation: this.renderImageLocation.bind(this)
        }

        this.state = {
            forms: {...props.forms},
            panelErrors: {},
            selectedPanel: props.selectedPanel,
            submit: false,
        }
        this.formNames = {}
        this.submit = {}
        this.singleForm = (Object.keys(props.forms).length === 1)
    }

    componentDidMount() {
        Object.keys(this.state.forms).map(name => {
            const form = this.state.forms[name]
            this.formNames[form.name] = name
            this.submit[form.name] = false
        })
        let panelErrors = {}
        if (this.singleForm) {
            panelErrors = setPanelErrors( {}, {})
        }
        let forms = checkHiddenRows({...this.state.forms})
        this.setMyState(forms, panelErrors)
    }

    loadGoogleBookData(){
        
        const isbn = this.state.forms.single.children.field6.value !== '' ? this.state.forms.single.children.field6.value : this.state.forms.single.children.field5.value
        if (isbn === '')
        {
            let form = {...this.state.forms.single}
            form.errors.push({'class': 'warning', 'message': this.translate('Please enter an ISBN13 or ISBN10 value before trying to get data from Google Books.')})
            let forms = {...this.state.forms}
            forms.single = form
            this.setMyState(forms)
            return
        }
        const route = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' + isbn
        fetchJson(
            route,
            [],
            false,
        ).then(data => {
            let form = {...this.state.forms.single}
            if (data['totalItems'] === 0) {
                form.errors.push({
                    'class': 'warning',
                    'message': this.translate('The specified record cannot be found.')
                })
                this.setMyState({'single': form})
                return
            }

            let item = data.items[0]
            form.children.name.value = item.volumeInfo.title
            let authors = '';
            for (let i = 0; i < item['volumeInfo']['authors'].length; i++) {
                authors = authors + item['volumeInfo']['authors'][i] + ', ';
            }

            form.children.producer.value = authors
            form.children.field1.value = item.volumeInfo.publisher
            let d = new Date(item.volumeInfo.publishedDate)
            form.children.field2.value = ('000' + d.getDate()).slice(-2) + '/' + ('000' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear()
            form.children.field7.value = item.volumeInfo.description
            if (item['volumeInfo']['industryIdentifiers'][0]['type'] === 'ISBN_10') {
                form.children.field5.value = item['volumeInfo']['industryIdentifiers'][0]['identifier']
            }
            if (item['volumeInfo']['industryIdentifiers'][1]['type'] === 'ISBN_13') {
                form.children.field6.value = item['volumeInfo']['industryIdentifiers'][1]['identifier']
            }

            form.children.field14.value = item['volumeInfo']['pageCount']

            let format = item.volumeInfo.printType.toLowerCase();
            format = format.charAt(0).toUpperCase() + format.slice(1);
            form.children.field0.value = format

            form.children.field19.value = item['volumeInfo']['infoLink']

            let image = typeof item['volumeInfo']['imageLinks'] !== 'undefined' ? item['volumeInfo']['imageLinks']['thumbnail'] : ''
            if (image !== '') {
                form.children.imageType.value = 'Link'
                form.children.imageLink.value = image
            }

            form.children.field18.value = item['volumeInfo']['language']

            let subjects = '';
            if (typeof item['volumeInfo']['categories'] !== 'undefined') {
                for (let i = 0; i < item['volumeInfo']['categories'].length; i++) {
                    subjects = subjects + item['volumeInfo']['categories'][i] + ', ';
                }
                form.children.field8.value = subjects.substring(0, (subjects.length - 2))
            }

            this.setMyState({'single': form})
            return
        }).catch(error => {
            let form = {...this.state.forms.single}
            form.errors.push({'class': 'error', 'message': error})
            this.setMyState(
                {'single': form}
            )
        })
    }


    selectLibraryAndType(e){
        let form = {...this.state.forms['single']}
        const id = e.target.id
        const value = e.target.value
        if (id === 'edit_library') {
            form.children.library.value = value
        }
        if (id === 'edit_itemType') {
            form.children.itemType.value = value
        }
        this.setMyState(
            {'single': form},
        )

        let choice = Object.keys(form.children.itemType.choices).filter(key => {
            let choice = form.children.itemType.choices[key]
            if (choice.value === form.children.itemType.value)
                return choice
        })

        if (form.children.library.value > 0 && choice !== []) {
            let data = {
                library: form.children.library.value,
                itemType: form.children.itemType.value,
                submit_clicked: 'library_type_select',
                _token: form.children._token.value,
            }
            fetchJson(
                form.action,
                {method: form.method, body: JSON.stringify(data)},
                false)
                .then(data => {
                    let errors = form.errors
                    errors = errors.concat(data.errors)
                    form = data.form
                    form.errors = errors
                    this.setMyState(
                        {'single': {...form}},
                        setPanelErrors(form, {}),
                        'General',
                    )
                }).catch(error => {
                    form.errors.push({'class': 'error', 'message': error})
                    this.setMyState(
                        {'single': {...form}},
                        setPanelErrors({...form}, {}),
                    )
                })
        }
    }

    setMyState(forms, panelErrors, selectedPanel){
        if (typeof panelErrors === 'undefined')
            panelErrors = this.state.panelErrors
        if (typeof selectedPanel === 'undefined')
            selectedPanel = this.state.selectedPanel
        if (typeof forms.forms !== 'undefined')
            forms = {...forms.forms}
        this.setState({
            forms: forms,
            panelErrors: panelErrors,
            selectedPanel: selectedPanel,
            submit: isSubmit(this.submit),
        })
    }

    translate(id){
        return trans(this.translations, id)
    }

    onSelectTab(tabIndex)
    {
        let selectedPanel = this.state.selectedPanel
        let i = 0
        Object.keys(this.panels).map(key => {
            if (i === tabIndex)
                selectedPanel = key
            i++
        })
        this.setState({
            selectedPanel: selectedPanel
        })
    }

    deleteFile(form) {
        let route = '/resource/' + btoa(form.value) + '/' + this.actionRoute + '/delete/'
        if (typeof form.delete_security !== 'undefined' && form.delete_security !== false)
            route = '/resource/' + btoa(form.value) + '/' + form.delete_security + '/delete/'
        let parentForm = getParentForm(this.state.forms,form)
        fetchJson(
            route,
            {},
            false)
            .then(data => {
                if (data.status === 'success') {
                    let errors = parentForm.errors
                    errors = errors.concat(data.errors)
                    parentForm.errors = errors
                    this.setMyState(
                        buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form), changeFormValue(parentForm,form,'')), this.singleForm)
                    )
                } else {
                    let errors = parentForm.errors
                    errors = errors.concat(data.errors)
                    parentForm.errors = errors
                    this.setMyState(
                        buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form), parentForm), this.singleForm)
                    )
                }
            }).catch(error => {
            let errors = parentForm.errors
            errors.push({'class': 'error', 'message': error})
            parentForm.errors = errors
            this.setMyState(
                buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form), parentForm), this.singleForm)
            )
        })
    }

    generateNewPassword(form) {
        const password = createPassword(form.generateButton.passwordPolicy)
        let fullForm = getParentForm(this.state.forms,form)
        let id = form.id.replace('first', 'second')
        fullForm = {...changeFormValue(fullForm,form,password)}
        let second = findElementById(fullForm, id, {})
        alert(form.generateButton.alertPrompt + ': ' + password)
        fullForm = changeFormValue(fullForm,second,password)
        this.setMyState(buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form),fullForm)))
    }

    onCKEditorChange(event, editor, form) {
        const data = editor.getData()
        this.setMyState(buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form), changeFormValue(getParentForm(this.state.forms,form),form,data))))
    }

    onElementChange(e, form) {
        const submitOnChange = form.submit_on_change
        let parentForm = getParentForm(this.state.forms,form)
        const parentName = getParentFormName(this.formNames,form)
        if (form.type === 'toggle') {
            let value = form.value === 'Y' ? 'N' : 'Y'
            this.setMyState(buildState(mergeParentForm(this.state.forms,parentName, changeFormValue(parentForm,form,value)), this.singleForm))
            return
        }
        if (form.type === 'file') {
            let value = e.target.files[0]
            let readFile = new FileReader()
            readFile.readAsDataURL(value)
            readFile.onerror = (e) => {
                parentForm.errors.push({'class': 'error', 'message': this.functions.translations('A problem occurred loading the file.')})
                this.setMyState(buildState(mergeParentForm(this.state.forms,parentName, changeFormValue(parentForm,form,value)), this.singleForm))
            }
            readFile.onload = (e) => {
                value = e.target.result
                this.setMyState(buildState(mergeParentForm(this.state.forms,parentName, changeFormValue(parentForm,form,value))))
            }
            return
        }
        let value = e.target.value
        form.value = value
        const newValue = changeFormValue({...parentForm},form,value)
        this.setMyState(buildState(mergeParentForm(this.state.forms,parentName, newValue), this.singleForm))
        if (submitOnChange)
            this.submitForm({},form)
    }

    submitForm(e,form) {
        const pressed = form.name
        const parentName = getParentFormName(this.formNames,form)
        if (this.submit[parentName]) return
        this.submit[parentName] = true
        this.setState({
            submit: true,
        })
        let parentForm = {...getParentForm(this.state.forms,form)}
        let data = buildFormData({}, parentForm)
        data['submit_clicked'] = pressed

        fetchJson(
            parentForm.action,
            {method: parentForm.method, body: JSON.stringify(data)},
            false)
            .then(data => {
                if (data.status === 'redirect') {
                    window.open(data.redirect,'_self');
                } else {
                    let errors = parentForm.errors
                    errors = errors.concat(data.errors)
                    let form = {...data.form}
                    form.errors = errors
                    this.submit[parentName] = false
                    this.setMyState(buildState({...mergeParentForm(this.state.forms, parentName,{...form})}, this.singleForm), setPanelErrors({...form}, {}))
                }
                }).catch(error => {
                    parentForm.errors.push({'class': 'error', 'message': error})
                    this.submit[parentName] = false
                    this.setMyState(buildState({...mergeParentForm(this.state.forms, parentName,{...parentForm})}, this.singleForm), setPanelErrors({...form}, {}))
            })
    }

    deleteElement(element) {
        let parentForm = getParentForm(this.state.forms,element)
        const restoreForm = parentForm
        parentForm = deleteFormElement(parentForm, element)
        this.setMyState(
            buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,element),parentForm), this.singleForm)
        )
        if (typeof element.never_saved !== 'boolean') {
            let id = element.id.replace('_' + element.name,'')
            let collection = findElementById(parentForm, id, {})
            let route = collection.element_delete_route
            if (typeof collection.element_delete_options !== 'object') collection.element_delete_options = {}
            let fetch = true
            Object.keys(collection.element_delete_options).map(search => {
                let replace = collection.element_delete_options[search]
                route = route.replace(search, element.children[replace].value)
                if (isEmpty(element.children[replace].value)) {
                    fetch = false
                }
            })
            if (fetch === false) return

            fetchJson(route, [], false)
                .then((data) => {
                    let errors = parentForm.errors
                    errors = errors.concat(data.errors)
                    parentForm.errors = errors
                    if (data.status === 'success') {
                        this.setMyState(
                            buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,element), parentForm), this.singleForm)
                        )
                    } else {
                        this.setMyState(
                            buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,element), {...restoreForm}), this.singleForm)
                        )
                    }
                }).catch(error => {
                parentForm = {...restoreForm}
                let errors = parentForm.errors
                errors.push({'class': 'error', 'message': error})
                parentForm.errors = errors
                this.setMyState(
                    buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,element), parentForm), this.singleForm)
                )
            })
        }
    }

    addElement(form) {
        let id = uuid()
        let element = {...replaceName({...form.prototype}, id)}
        let parentForm = {...getParentForm(this.state.forms,form)}
        let parentFormName = getParentFormName(this.formNames,form)
        element.children.id.value = id
        if (typeof form.children === 'object'){
            let newChildren = []
            Object.keys(form.children).map(key => {
                newChildren.push({...form.children[key]})
            })
            form.children = newChildren
        }
        if (typeof form.children === 'undefined')
            form.children = []

        element.never_saved = true

        form.children.push({...element})

        parentForm = {...replaceFormElement(parentForm, form)}

        this.setMyState(buildState({...mergeParentForm(this.state.forms,parentFormName,parentForm)}, this.singleForm))
    }

    renderImageLocation(e) {
        let form = {...this.state.forms.single}
        form.children.imageType.value = e.target.value
        if (e.target.value === 'Link') {
            form.children.imageLocation.type = 'url'
            form.children.imageLocation.label = this.translate('Image Link')
        } else {
            form.children.imageLocation.type = 'file'
            form.children.imageLocation.label = this.translate('Image File')
        }
        let parentForm = {...getParentForm(this.state.forms,form)}
        let parentFormName = getParentFormName(this.formNames,form)

        console.log(form)
        console.log(e.target.value)
        this.setMyState(buildState({...mergeParentForm(this.state.forms,parentFormName,parentForm)}, this.singleForm))
    }

    render() {
        return (
            <section>
                {this.state.submit ? <div className={'waitOne info'}>{this.functions.translate('Let me ponder your request')}...</div> : ''}
                <PanelApp panels={this.panels} selectedPanel={this.state.selectedPanel} functions={this.functions} forms={this.state.forms} actionRoute={this.actionRoute} singleForm={this.singleForm} translations={this.translations} panelErrors={this.state.panelErrors} />
            </section>
        )
    }
}

LibraryApp.propTypes = {
    panels: PropTypes.object,
    forms: PropTypes.object,
    translations: PropTypes.object,
    content: PropTypes.string,
    actionRoute: PropTypes.string,
    selectedPanel: PropTypes.string,
}

LibraryApp.defaultProps = {
    functions: {},
    translations: {},
    forms: {},
}

