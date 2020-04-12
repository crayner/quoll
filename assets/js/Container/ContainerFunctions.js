'use strict';

import React from 'react'
import {isEmpty} from "../component/isEmpty"
import {openPage} from "../component/openPage"
import {fetchJson} from "../component/fetchJson"

export function setPanelErrors(form, panelErrors)
{
    if (typeof form.children === 'undefined') {
        return panelErrors
    }
    Object.keys(form.children).map(key => {
        const child = form.children[key]
        setPanelErrors(child,panelErrors)
        if (Object.keys(child.errors).length > 0 && child.panel !== false) {
            if (typeof panelErrors[child.panel] === 'undefined')
                panelErrors[child.panel] = {}
            panelErrors[child.panel].problem = true
        }
    })
    return panelErrors
}

export function getControlButtons(returnRoute, addRoute, functions) {
    let control = []
    if (returnRoute !== '') {
        control.push(<a key={'remove'} className={'close-button gray ml-3'} onClick={(e) => functions.handleAddClick(returnRoute, '_self')} title={functions.translate('Return')}><span className={'fas fa-reply fa-fw text-gray-800 hover:text-purple-500'}/></a>)
    }
    if (addRoute !== '') {
        control.push(<a key={'add'} className={'close-button gray ml-3'} onClick={(e) => functions.handleAddClick(addRoute, '_self')} title={functions.translate('Add')}><span className={'fas fa-plus-circle fa-fw text-gray-800 hover:text-purple-500'}/></a>)
    }
    return control
}

export function trans(translations,id){
    if (isEmpty(translations[id])) {
        console.error('Unable to translate: ' + id)
        return id
    }
    return translations[id]
}

export function downloadFile(form) {
    const file = form.value
    let route = '/resource/' + btoa(file) + '/' + this.actionRoute + '/download/'
    if (typeof form.delete_security !== 'undefined' && form.delete_security !== false)
        route = '/resource/' + btoa(form.value) + '/' + form.delete_security + '/download/'
    openPage(route, {target: '_blank'}, false)
}

export function openUrl(file, target) {
    if (typeof target === 'undefined')
        target = '_blank'
    let options = ''
    if (typeof file === 'object') {
        options = file
        file = options['url']
        target = options['target']
        options = typeof options['options'] !== 'undefined' ? options['options'] : ''
    }
    window.open(file,target,options)
}

export function buildState(forms,singleForm){
    let panelErrors = {}
    let state = {}
    if (singleForm) {
        panelErrors = setPanelErrors(forms[Object.keys(forms)[0]], panelErrors)
    }
    state = {
        forms: forms,
        panelErrors: panelErrors,
    }
    return state
}

export function getParentForm(forms,form,formNames) {
    if (typeof formNames === 'undefined') {
        formNames = {}
        Object.keys(forms).map(key => {
            const child = forms[key]
            formNames[child.name] = key
        })
    }
    return forms[getParentFormName(formNames,form)]
}

export function getParentFormName(formNames,form) {
    return formNames[form.full_name.substring(0, form.full_name.indexOf('['))]
}

export function mergeParentForm(forms, name, form){
    forms[name] = {...form}
    return {...forms}
}

export function replaceFormElement(form, element) {
    if (typeof form.children === 'object') {
        Object.keys(form.children).map(key => {
            let child = replaceFormElement(form.children[key],element)
            if (child.id === element.id)
                form.children[key] = element
        })
    } else if (typeof form.children === 'array') {
        form.children.map((child,key) => {
            child = replaceFormElement(child,element)
            if (child.id === element.id)
                form.children[key] = element
        })
    }
    if (form.id === element.id)
        form = element
    return form
}

export function replaceName(element, id) {
    element = {...element}
    if (typeof element.children === 'object') {
        element.children = {...element.children}
        Object.keys(element.children).map(childKey => {
            let child = replaceName(element.children[childKey], id)
            element.children[childKey] = child
        })
    }
    element.name = element.name.replace('__name__', id)
    element.id = element.id.replace('__name__', id)
    element.full_name = element.full_name.replace('__name__', id)
    if (typeof element.chained_child === 'string')
        element.chained_child = element.chained_child.replace('__name__', id)
    if (typeof element.label === 'string')
        element.label = element.label.replace('__name__', id)
    return element
}

export function deleteFormElement(form,element){
    if (typeof form.children === 'object') {
        Object.keys(form.children).map(key => {
            let child = deleteFormElement(form.children[key],element)
            if (child.id === element.id) {
                delete form.children[key]
            }
        })
    }
    if (typeof form.children === 'array') {
        form.children.map((child,key) => {
            child = deleteFormElement(child,element)
            if (child.id === element.id) {
                form.children.splice(key, 1)
            }
        })
    }
    return form
}

export function changeFormValue(form, find, value) {
    let newForm = {...form}
    if (typeof newForm.children === 'object' && Object.keys(newForm.children).length > 0) {
        let george = {...newForm.children}
        Object.keys(george).map(key => {
            let child = {...george[key]}
            if (child.id === find.id) {
                child.value = value
                Object.assign(george[key], {...child})
                if (typeof child.visibleByClass !== 'undefined')
                    toggleRowsOnValue(value,child)
            } else {
                Object.assign(george[key], changeFormValue({...child}, find, value))
            }
        })
        Object.assign(newForm.children, {...george})
        return {...newForm}
    } else {
        return {...newForm}
    }
}

export function checkHiddenRows(forms) {
    Object.keys(forms).map(key => {
        let form = forms[key]
        forms[key] = visibleByClassInitial(form, createVisibleSets(form, {}))
    })

    return {...forms}
}

function visibleByClass(form) {
    if (typeof form.children !== 'undefined') {
        Object.keys(form.children).map(key => {
            let child = form.children[key]
            visibleByClass(child)
        })
    }
    if (typeof form.visibleByClass !== 'undefined' && form.visibleByClass !== false){
        toggleRowsOnValue(form.value,form)
    }
    return form
}

function createVisibleSets(form, visibleSets) {
    if (typeof form.children !== 'undefined') {
        Object.keys(form.children).map(key => {
            let child = form.children[key]

            if (typeof child.visibleByClass !== 'undefined' && child.visibleByClass !== false){
                var x = false
                if (child.value === child.visibleWhen)
                    x = true
                visibleSets[child.visibleByClass] = x
            }
            visibleSets = createVisibleSets(child,visibleSets)
        })
    }
    return visibleSets
}

function visibleByClassInitial(form, visibleSets) {
    if (typeof form.children !== 'undefined') {
        Object.keys(form.children).map(key => {
            let child = form.children[key]
            visibleByClassInitial(child,visibleSets)
        })
    }

    Object.keys(visibleSets).map(name => {
        let x = visibleSets[name]
        if (typeof form.row_class === 'string' && form.row_class.includes(name)) {
            let row_class = form.row_class
            if (!row_class.includes('hiddenSlider'))
                row_class = row_class + ' hiddenSlider'
            if (!x && !row_class.includes('close'))
                row_class = row_class + ' close'
            form.row_class = row_class
        }
    })

    return form
}

export function toggleRowsOnValue(value, form) {
    let elements = document.getElementsByClassName(form.visibleByClass)
    Object.keys(elements).map(key => {
        let child = elements[key]
        if (!child.classList.contains('hiddenSlider'))
            child.classList.toggle('hiddenSlider')
    })

    if (value === form.visibleWhen) {
        // Show the elements
        Object.keys(elements).map(key => {
            let child = elements[key]
            if (child.classList.contains('close'))
                child.classList.toggle('close')
        })
    } else {
        // Hide the elements
        Object.keys(elements).map(key => {
            let child = elements[key]
            if (!child.classList.contains('close'))
                child.classList.toggle('close')
        })
    }
}

export function isSubmit(submit) {
    let result = false
    Object.keys(submit).map(key => {
        if (submit[key])
            result = true
    })
    return result
}

export function findElementById(form, id, element) {
    if (typeof element.id === 'string' && element.id === id)
        return element
    if (typeof form.children === 'object') {
        Object.keys(form.children).map(key => {
            let child = form.children[key]
            if (child.id === id)
                element = child
            element = findElementById(form.children[key],id,element)
        })
        return element
    }
    if (typeof form.children === 'array') {
        form.children.map((child, key) => {
            if (child.id === id)
                element = child
            element = findElementById(child,id,element)
        })
        return element
    }
    return element
}

export function buildFormData(data, form) {
    if (form.type === 'expanded_choice') {
        return form.value
    }
    if (form.type === 'date') {
        if (typeof form.value.date !== 'undefined')
            return form.value.date.toString().slice(0,10)
        return form.value
    }
    if (typeof form.children === 'object' && Object.keys(form.children).length > 0) {
        Object.keys(form.children).map(key => {
            let child = form.children[key]
                data[child.name] = buildFormData({}, child)
        })
        return data
    } else if (typeof form.children === 'array' && form.children.length > 0) {
            form.children.map(child => {
                data[child.name] = buildFormData({}, child)
            })
            return data
    } else {
        return form.value
    }
}

export function initialContentLoaders(loaders, contentManager) {
    if (loaders === null) return

    loaders.map(loader => {
        setTimeout(contentLoader(loader, contentManager), 50)
    })
}

export function contentLoader(loader, contentManager) {
    fetchJson(loader.route, {}, false)
        .then(data => {
            if (data.status === 'success')
                contentManager(loader, data.content)

            if (loader.timer > 0)
                setTimeout(contentLoader(loader,contentManager), loader.timer)
        })
}

export function checkChainedElements(forms, formNames)
{
    Object.keys(forms).map(key => {
        forms[key] = checkChainedFormElement(forms[key], forms, formNames)
    })

    return {...forms}
}

function checkChainedFormElement(form, forms, formNames)
{
    if (typeof form.children !== 'undefined' && Object.keys(form.children).length > 0) {
        Object.keys(form.children).map(key => {
            form.children[key] = checkChainedFormElement(form.children[key], forms, formNames)
        })
    }
    if (form.type === 'choice') {
        if (typeof form.chained_child !== 'undefined' && form.chained_child !== null) {
            forms = setChainedSelect(form, forms, formNames)
        }
    }

    return {...form}
}

export function setChainedSelect(form, forms, formNames)
{
    forms = {...forms}
    let parent = {...getParentForm(forms, form, formNames)}
    const parentName = getParentFormName(formNames,form)

    let child = findElementById(parent, form.chained_child, {})
    const value = form.value
    let choices = form.chained_values[value]
    if (typeof choices !== 'object' || Object.keys(choices).length === 0 && parseInt(value) > 0) {
        choices = form.chained_values["" + value]
    }

    if (typeof choices !== 'object' || Object.keys(choices).length === 0) {
        child.disabled = true
        child.choices = {}
    } else {
        child.disabled = false
        child.choices = {...choices}
        if (Object.keys(child.choices).length === 0)
            child.disabled = true
    }
    forms = {...mergeParentForm(forms,parentName,parent)}
    return {...forms}
}
