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
    isSubmit
} from "../Container/ContainerFunctions"

export default class DepartmentEditApp extends Component {
    constructor (props) {
        super(props)
        this.panels = props.panels ? props.panels : {}
        this.content = props.content ? props.content : null
        this.functions = props.functions
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
            manageLinkOrFile: this.manageLinkOrFile.bind(this),
        }
        this.state = {
            selectedPanel: props.selectedPanel,
            forms: {...props.forms},
            panelErrors: {}
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
                panelErrors = setPanelErrors({}, panelErrors)
        }
        this.manageURLTypes({...this.state.forms.single}, panelErrors)
    }

    setMyState(forms, panelErrors){
        this.manageURLTypes({...forms.single}, panelErrors)
    }

    manageURLTypes(parentForm, panelErrors) {
        parentForm.children.resources.children.map((child,key) => {
            if (child.children.type.value === 'File') {
                parentForm.children.resources.children[key].children.url.type = 'file'
            } else {
                parentForm.children.resources.children[key].children.url.type = 'url'
                parentForm.children.resources.children[key].children.type.value = 'Link'
            }
        })
        this.setState({
            forms: {single: parentForm},
            panelErrors: panelErrors,
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
                    this.setState(
                        buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form), changeFormValue(parentForm,form,'')), this.singleForm)
                    )
                } else {
                    let errors = parentForm.errors
                    errors = errors.concat(data.errors)
                    parentForm.errors = errors
                    this.setState(
                        buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form), parentForm), this.singleForm)
                    )
                }
            }).catch(error => {
            let errors = parentForm.errors
            errors.push({'class': 'error', 'message': error})
            parentForm.errors = errors
            this.setState(
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
        this.setState(buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form),fullForm)))
    }

    onCKEditorChange(event, editor, form) {
        const data = editor.getData()
        this.setState(buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form), changeFormValue(getParentForm(this.state.forms,form),form,data))))
    }

    onElementChange(e, form) {
        const submitOnChange = form.submit_on_change
        let parentForm = getParentForm(this.state.forms,form)
        const parentName = getParentFormName(this.formNames,form)
        if (form.type === 'toggle') {
            let value = form.value === 'Y' ? 'N' : 'Y'
            this.setState(buildState(mergeParentForm(this.state.forms,parentName, changeFormValue(parentForm,form,value)), this.singleForm))
            return
        }
        if (form.type === 'file') {
            let value = e.target.files[0]
            let readFile = new FileReader()
            readFile.readAsDataURL(value)
            readFile.onerror = (e) => {
                parentForm.errors.push({'class': 'error', 'message': this.functions.translations('A problem occurred loading the file.')})
                this.setState(buildState(mergeParentForm(this.state.forms,parentName, changeFormValue(parentForm,form,value)), this.singleForm))
            }
            readFile.onload = (e) => {
                value = e.target.result
                this.setState(buildState(mergeParentForm(this.state.forms,parentName, changeFormValue(parentForm,form,value))))
            }
            return
        }
        let value = e.target.value
        form.value = value
        const newValue = changeFormValue({...parentForm},form,value)
        this.setState(buildState(mergeParentForm(this.state.forms,parentName, newValue), this.singleForm))
        if (submitOnChange)
            this.submitForm({},form)
    }

    submitForm(e,form) {
        const parentName = getParentFormName(this.formNames,form)
        if (this.submit[parentName]) return
        this.submit[parentName] = true
        this.setState(buildState({...this.state.forms}, this.singleForm))
        let parentForm = {...getParentForm(this.state.forms,form)}
        let data = buildFormData({}, parentForm)
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
                    let form = typeof this.functions.submitFormCallable === 'function' ? this.functions.submitFormCallable(data.form) : data.form
                    form.errors = errors
                    this.submit[parentName] = false
                    this.setState(buildState({...mergeParentForm(this.state.forms,parentName, {...form})}, this.singleForm))
                }
            }).catch(error => {
            parentForm.errors.push({'class': 'error', 'message': error})
            this.submit[parentName] = false
            this.setState(buildState({...mergeParentForm(this.state.forms,parentName, {...parentForm})}, this.singleForm))
        })
    }

    deleteElement(element) {
        let parentForm = getParentForm(this.state.forms,element)
        const restoreForm = parentForm
        parentForm = deleteFormElement(parentForm, element)
        this.setState(
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
                if (element.children[replace].value === null) {
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
                        if (typeof this.functions.deleteElementCallable === 'function') element = this.functions.deleteElementCallable(data, element)
                        this.setState(
                            buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,element), parentForm), this.singleForm)
                        )
                    } else {
                        this.setState(
                            buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,element), {...restoreForm}), this.singleForm)
                        )
                    }
                }).catch(error => {
                parentForm = {...restoreForm}
                let errors = parentForm.errors
                errors.push({'class': 'error', 'message': error})
                parentForm.errors = errors
                this.setState(
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

        if (typeof form.children === 'undefined') {
            form.children = []
        } else {
            element.children.type.value = 'Link'
            element.children.url.type = 'url'
            element.children.department.value = this.state.forms.single.children.id.value
        }

        element.never_saved = true

        form.children.push({...element})

        parentForm = {...replaceFormElement(parentForm, form)}

        this.setState(buildState({...mergeParentForm(this.state.forms,parentFormName,parentForm)}, this.singleForm))
    }
    
    manageLinkOrFile(e, form) {
        let name = form.id.replace('department_edit_resources_', '')
        name = name.replace('_type', '')
        let child = {}
        let childKey = null
        let parentForm = {...this.state.forms.single}
        parentForm.children.resources.children.map((x,key) => {
            if (name === x.name) {
                child = x
                childKey = key
            }
        })
        let value = e.target.value
        if (value === 'File') {
            child.children.url.type = 'file'
            child.children.type.value = 'File'
        } else {
            child.children.url.type = 'url'
            child.children.type.value = 'Link'
        }
        parentForm.children.resources.children[childKey] = child
        this.setMyState({single: parentForm})
    }


    render() {
        return (
            <section>
                {isSubmit(this.submit)  ? <div className={'waitOne info'}>{this.functions.translate('Let me ponder your request')}...</div> : ''}
                <PanelApp panels={this.panels} selectedPanel={this.state.selectedPanel} functions={this.functions} forms={this.state.forms} actionRoute={this.actionRoute} singleForm={this.singleForm} translations={this.translations} panelErrors={this.state.panelErrors} />
            </section>
        )
    }
}

DepartmentEditApp.propTypes = {
    panels: PropTypes.object,
    forms: PropTypes.object,
    functions: PropTypes.object,
    translations: PropTypes.object,
    content: PropTypes.string,
    actionRoute: PropTypes.string,
    selectedPanel: PropTypes.string,
}

DepartmentEditApp.defaultProps = {
    functions: {},
    translations: {},
    forms: {},
}

