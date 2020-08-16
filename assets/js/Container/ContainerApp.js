'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
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
    checkHiddenRows,
    initialContentLoaders,
    checkChainedElements,
    setVisibleKeysByForm,
    getControlButtons,
    setChainedSelect
} from "./ContainerFunctions"
import {isEmpty} from "../component/isEmpty"
import uuid from "react-uuid"

export default class ContainerApp extends Component {
    constructor (props) {
        super(props)
        this.content = props.content ? props.content : null
        this.returnRoute = props.returnRoute
        this.addElementRoute = props.addElementRoute
        this.translations = props.translations
        this.actionRoute = props.actionRoute
        this.showSubmitButton = props.showSubmitButton ? props.showSubmitButton : false
        this.hideSingleFormWarning = props.hideSingleFormWarning

        this.functions = {
            translate: props.functions.translate,
            mergeTranslations: props.functions.mergeTranslations,
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
            refreshChoiceList: this.refreshChoiceList.bind(this),
            addElementToChoice: this.addElementToChoice.bind(this),
            removeSimpleArrayValue: this.removeSimpleArrayValue.bind(this),
            moveSimpleArrayChild: this.moveSimpleArrayChild.bind(this),
            addSimpleArrayValue: this.addSimpleArrayValue.bind(this),
            toggleExpandedAllNone: this.toggleExpandedAllNone.bind(this),
            handleAddClick: props.functions.handleAddClick,
            replaceSpecialContent: this.replaceSpecialContent.bind(this),
            mergeSubForm: this.mergeSubForm.bind(this),
            getContent: props.functions.getContent,
            callRoute: this.callRoute.bind(this)
        }

        this.contentManager = this.contentManager.bind(this)

        if (Object.keys(props.panels).length === 0 && this.content !== null) {
            props.panels['default'] = {}
            props.panels.default['name'] = 'default'
            props.panels.default['disabled'] = true
            props.panels.default['content'] = this.content
        }

        this.state = {
            selectedPanel: props.selectedPanel,
            forms: {...props.forms},
            panelErrors: {},
            submit: false,
            content: {},
            panels: props.panels ? props.panels : {},
            visibleKeys: {}
        }

        this.formNames = {}
        this.submit = {}
        this.expandedAllNoneChecked = {}
        this.singleForm = (Object.keys(props.forms).length === 1 && Object.keys(props.forms)[0] === 'single')
        this.contentLoaders = props.contentLoader
        this.popup = props.popup
    }

    componentDidMount() {
        Object.keys(this.state.forms).map(name => {
            const form = this.state.forms[name]
            this.formNames[form.name] = name
            this.submit[form.name] = false
        })
        let panelErrors = {}
        if (this.singleForm) {
            panelErrors = setPanelErrors({}, {})
        }
        initialContentLoaders(this.contentLoaders, this.contentManager)
        let forms = checkHiddenRows({...this.state.forms}, {})
        let visibleKeys = forms.visible_keys
        delete forms.visible_keys
        forms = checkChainedElements(forms, this.formNames)
        this.setMyState(forms, panelErrors, visibleKeys)
    }

    contentManager(loader,content) {
        if (typeof loader === 'undefined' || typeof content === 'undefined')
            return false

        let fullContent = this.state.content
        fullContent[loader.target] = {loader: loader, content: content}
        this.setState({
            content: {...fullContent}
        })
        return true
    }

    setMyState(forms, panelErrors, visibleKeys){
        if (typeof forms.panelErrors !== 'undefined') {
            panelErrors = forms.panelErrors
            forms = {...forms.forms}
        }
        if (typeof visibleKeys === 'undefined') {
            visibleKeys = {...this.state.visibleKeys}
        }

        if (typeof panelErrors === 'undefined')
            panelErrors = this.state.panelErrors

        this.setState({
            forms: forms,
            panelErrors: panelErrors,
            submit: isSubmit(this.submit),
            visibleKeys: visibleKeys,
        })
    }

    onSelectTab(tabIndex)
    {
        let selectedPanel = this.state.selectedPanel
        let i = 0
        Object.keys(this.state.panels).map(key => {
            if (i === tabIndex)
                selectedPanel = key
            i++
        })
        this.setState({
            selectedPanel: selectedPanel
        })
    }

    deleteFile(form) {
        if (typeof form.delete_route === 'undefined' || form.delete_route === false) {
            console.info('You need to set the form delete_route for ' + form.full_name + ' for deletion of this file.' )
            return
        }
        let route = form.delete_route
        let parentForm = getParentForm(this.state.forms,form)
        fetchJson(
            route,
            {},
            false)
            .then(data => {
                if (data.status === 'redirect') {
                    window.open(data.redirect,'_self')
                } else if (data.status === 'success') {
                    let errors = parentForm.errors
                    errors = errors.concat(data.errors)
                    parentForm.errors = errors
                    form.photo.exists = false
                    form.photo.url = ''
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
        let parentForm = getParentForm(this.state.forms,form)
        let id = form.id.replace('first', 'second')
        parentForm = {...changeFormValue(parentForm,form,password)}
        let second = findElementById(parentForm, id, {})
        id = id.replace('_second', '')
        let parent = findElementById(parentForm, id, {})
        alert(form.generateButton.alertPrompt + ': ' + password)
        let parentValue = {
            first: password,
            second: password
        }
        parentForm = changeFormValue(parentForm,parent,parentValue)
        parentForm = changeFormValue(parentForm,second,password)
        this.setMyState(buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form),parentForm)))
    }

    onCKEditorChange(event, editor, form) {
        const data = editor.getData()
        this.setMyState(buildState(mergeParentForm(this.state.forms,getParentFormName(this.formNames,form), changeFormValue(getParentForm(this.state.forms,form),form,data))))
    }

    onElementChange(e, form) {
        const submitOnChange = form.submit_on_change
        let forms = {...this.state.forms}
        let parentForm = getParentForm(forms,form,this.formNames)
        const parentName = getParentFormName(this.formNames,form)
        if (form.type === 'toggle') {
            let value = form.value === 'N' ? 'Y' : 'N'
            forms = mergeParentForm(forms, parentName, changeFormValue(parentForm, form, value))
            let visibleKeys = setVisibleKeysByForm(form,{...this.state.visibleKeys})
            this.setMyState(buildState(forms, this.singleForm), { ...this.state.panelErrors }, visibleKeys)
            return
        }
        if (form.type === 'file') {
            let value = e.target.files[0]
            let readFile = new FileReader()
            readFile.readAsDataURL(value)
            readFile.onerror = (e) => {
                parentForm.errors.push({'class': 'error', 'message': this.functions.translations('A problem occurred loading the file.')})
                this.setMyState(buildState(mergeParentForm(forms,parentName, changeFormValue(parentForm,form,value)), this.singleForm))
            }
            readFile.onload = (e) => {
                value = e.target.result
                this.setMyState(buildState(mergeParentForm(forms,parentName, changeFormValue(parentForm,form,value))))
            }
            return
        }
        let value = e.target.value
        if (form.type === 'choice' && form.multiple) {
            value = []
            const options = e.target.options
            for (var i = 0, l = options.length; i < l; i++) {
                if (options[i].selected) {
                    value.push(options[i].value);
                }
            }
        }
        if (form.parse_value === false) {
            form.value = value
        } else {
            let theFunction = require('../Functions/' + form.parse_value + '.js').default
            parentForm = theFunction(value, form, parentForm)
            value = parentForm.children[form.name].value
        }

        forms = {...mergeParentForm({...forms},parentName,changeFormValue(parentForm,form,value))}

        let visibleKeys = {...this.state.visibleKeys}
        if (form.type === 'choice') {
            if (form.chained_child !== null)
                forms = setChainedSelect(form,forms,this.formNames)
            if (form.visible_by_choice !== false) {
                visibleKeys = setVisibleKeysByForm(form, visibleKeys)
            }
        }

        this.setMyState(buildState(forms, this.singleForm), { ...this.state.panelErrors }, visibleKeys)
        if (submitOnChange)
            this.submitForm({},form)
    }

    toggleExpandedAllNone(name,toggle)
    {
        if (typeof this.expandedAllNoneChecked[name] === 'undefined')
            this.expandedAllNoneChecked[name] = false
        if (toggle)
            this.expandedAllNoneChecked[name] = !this.expandedAllNoneChecked[name];
         return this.expandedAllNoneChecked[name]
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
        if (this.showSubmitButton)
            data['submit_clicked'] = pressed
        if (isEmpty(parentForm.action)) {
            console.error('The form does not have an action set')
            this.setState({
                submit: false
            })
            return
        }
        fetchJson(
            parentForm.action,
            {method: parentForm.method, body: JSON.stringify(data)},
            false)
            .then(data => {
                if (data.status === 'redirect') {
                    this.functions.getContent(data.redirect)
                } else if (data.status === 'newPage') {
                     openUrl(data.redirect, '_self')
                } else {
                    let errors = parentForm.errors
                    errors = errors.concat(data.errors)
                    let form = {...data.form}
                    form.errors = errors
                    this.submit[parentName] = false
                    let forms = checkHiddenRows({...mergeParentForm(this.state.forms, parentName, {...form})})
                    let visibleKeys = forms.visible_keys
                    delete forms.visible_keys
                    forms = checkChainedElements(forms, this.formNames)
                    this.setMyState(buildState(forms, this.singleForm), setPanelErrors({...form}, {}), visibleKeys)
                }
            }).catch(error => {
                parentForm.errors.push({'class': 'error', 'message': error})
                this.submit[parentName] = false
                this.setMyState(buildState({...mergeParentForm(this.state.forms,parentName, {...parentForm})}, this.singleForm), setPanelErrors({...form}, {}))
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
            const parentName = getParentFormName(this.formNames,element)
            this.submit[parentName] = true
            this.setState({
                submit: true,
            })

            fetchJson(route, [], false)
                .then((data) => {
                    let errors = parentForm.errors
                    errors = errors.concat(data.errors)
                    parentForm.errors = errors
                    this.submit[parentName] = false
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
                    this.submit[parentName] = false
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

    replaceSpecialContent(name, content)
    {
        let panels = {...this.state.panels}
        if (typeof panels[name] === 'object' && typeof panels[name].special === 'object') {
            let special = {...panels[name].special}
            Object.keys(content).map(key => {
                if (typeof special[key] !== 'undefined') {
                    Object.assign(special[key], content[key])
                }
            })
            Object.assign(panels[name].special, {...special})
            this.setState({
                panels: {...panels}
            })
        }
    }

    addElement(form) {
        let id = uuid()
        let forms = {...this.state.forms}
        let element = {...replaceName({...form.prototype}, id)}
        let parentForm = {...getParentForm(forms,form)}
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

        forms = {...mergeParentForm(forms,parentFormName,parentForm)}
        forms = checkChainedElements(forms,this.formNames)
        this.setMyState(buildState(forms, this.singleForm))
    }

    refreshChoiceList(form) {
        let parentForm = {...getParentForm(this.state.forms,form)}
        const parentName = getParentFormName(this.formNames,form)
        fetchJson(
            form.auto_refresh_url,
            {},
            false)
            .then(data => {
                if (typeof data.choices === 'object' && typeof data.choices !== 'array') {
                    const choices = Object.keys(data.choices).map(key => {
                        return data.choices[key]
                    })
                    form.choices = choices
                } else {
                    form.choices = data.choices
                }
                parentForm = {...replaceFormElement(parentForm, form)}
                parentForm.errors.push({'class': 'info', 'message': this.translate('The list has been refreshed.')})
                this.setMyState(buildState({...mergeParentForm(this.state.forms,parentName,parentForm)}, this.singleForm))
            }).catch(error => {
                parentForm.errors.push({'class': 'error', 'message': error})
                this.submit[parentName] = false
                this.setMyState(buildState({...mergeParentForm(this.state.forms,parentName, {...parentForm})}, this.singleForm), setPanelErrors({...form}, {}))
            }
        )
    }

    addElementToChoice(e,url)
    {
        e.preventDefault()
        openUrl(url)
    }

    removeSimpleArrayValue(form,parent)
    {
        let key = form.name
        parent.children.splice(key,1)

        let values = []
        let children = []
        parent.children.map((child,key) => {
            child.id = form.id + '_' + key
            child.name = key
            child.full_name = form.full_name + '[' + key + ']'
            values.push(child.value)
            children.push(child)
        })
        parent.value = values
        parent.children = children

        let parentForm = {...getParentForm(this.state.forms,parent)}
        const parentName = getParentFormName(this.formNames,parent)
        this.setMyState(buildState(mergeParentForm(this.state.forms,parentName,changeFormValue(parentForm,parent,parent.value)),this.singleForm))
    }


    moveSimpleArrayChild(form,parent,direction)
    {
        let found = false
        let list = parent.children
        let item = list.pop()
        if (item.value !== '') {
            list.push(item)
        }
        let before = list.filter(item => {
            if (item.value === form.value) {
                found = true
            }
            if (found === false) {
                return item
            }
        })
        found = false
        let after = list.filter(item => {
            if (item.value === form.value) {
                found = true
            }
            if (found === true) {
                return item
            }
        })

        item = after.shift()
        if (direction === 'down' && after.length === 0) {
            return
        }
        if (direction === 'up' && before.length === 0) {
            return
        }

        if (direction === 'up') {
            let swap = before.pop()
            list = before
            list.push(item)
            list.push(swap)
            after.map(item => {
                list.push(item)
            })
        }
        if (direction === 'down') {
            let swap = after.shift()
            list = before
            list.push(swap)
            list.push(item)
            after.map(item => {
                list.push(item)
            })
        }

        let values = []
        let children = []
        list.map((child,key) => {
            child.id = form.id + '_' + key
            child.name = key
            child.full_name = form.full_name + '[' + key + ']'
            values.push(child.value)
            children.push(child)
        })
        parent.value = values
        parent.children = children

        let parentForm = {...getParentForm(this.state.forms,parent)}
        const parentName = getParentFormName(this.formNames,parent)
        this.setMyState(buildState(mergeParentForm(this.state.forms,parentName,changeFormValue(parentForm,parent,parent.value)),this.singleForm))
        this.addSimpleArrayValue(parent)
    }

    addSimpleArrayValue(form)
    {
        let value = form.children[form.children.length - 1].value
        if (isEmpty(value)) return
        const key = form.children.length
        let child = {...form.prototype}
        child.id = form.id + '_' + key
        child.name = key
        child.value = ''
        child.full_name = form.full_name + '[' + key + ']'
        form.children.push(child)
        let values = []
        const children = []
        form.children.map((child,key) => {
            child.id = form.id + '_' + key
            child.name = key
            child.full_name = form.full_name + '[' + key + ']'
            values.push(child.value)
            children.push(child)
        })
        form.value = values
        form.children = children
        let parentForm = {...getParentForm(this.state.forms,form)}
        const parentName = getParentFormName(this.formNames,form)
        this.setMyState(buildState(mergeParentForm(this.state.forms,parentName,changeFormValue(parentForm,form,form.value)),this.singleForm))
    }

    mergeSubForm(form,parent,id)
    {
        if (typeof parent === 'undefined') {
            parent = getParentForm({ ...this.state.forms }, form, this.formNames)
            let name = getParentFormName(this.formNames, parent)
            id = parent.id
        }
        if (typeof parent.children !== 'undefined') {
            Object.keys(parent.children).map(key => {
                let child = { ...parent.children[key] }
                if (child.id === form.id) {
                    Object.assign(parent.children[key], { ...form })
                } else {
                    child = this.mergeSubForm(form, child, id)
                    Object.assign(parent.children[key], { ...child })
                }
            })
        }
        if (id === parent.id) {
            this.setState({
                forms: mergeParentForm(this.state.forms, name, parent)
            })
        }
        return parent
    }

    callRoute(form) {
        let parentForm = {...getParentForm(this.state.forms,form)}
        const parentName = getParentFormName(this.formNames,form)
        fetchJson(
            form.on_click.route,
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
                } else if (data.status === 'redirect') {
                    window.open(data.redirect,'_self')
                }
            }).catch(error => {
                parentForm.errors.push({'class': 'error', 'message': error})
                this.submit[parentName] = false
                this.setMyState(buildState({...mergeParentForm(this.state.forms,parentName, {...parentForm})}, this.singleForm), setPanelErrors({...form}, {}))
            }
        )
    }

    render() {
        return (
            <section className={'containerApp'}>
                {this.state.submit ? <div className={'waitOne info'}>{this.functions.translate('Let me ponder your request')}...</div> : ''}
                {getControlButtons(this.returnRoute,this.addElementRoute,this.functions)}
                <PanelApp panels={this.state.panels} selectedPanel={this.state.selectedPanel} hideSingleFormWarning={this.hideSingleFormWarning} functions={this.functions} forms={this.state.forms} actionRoute={this.actionRoute} singleForm={this.singleForm} translations={this.translations} panelErrors={this.state.panelErrors} content={this.state.content} visibleKeys={this.state.visibleKeys} />
            </section>
        )
    }
}

ContainerApp.propTypes = {
    panels: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.array
    ]),
    forms: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.array
    ]),
    translations: PropTypes.object,
    content: PropTypes.string,
    actionRoute: PropTypes.string,
    selectedPanel: PropTypes.string,
    returnRoute: PropTypes.object,
    addElementRoute: PropTypes.object,
}

ContainerApp.defaultProps = {
    panels: {},
    translations: {},
    forms: {},
}
