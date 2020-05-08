'use strict'

import React from "react"
import PropTypes from 'prop-types'
import {widgetAttr, wrapperAttr} from './buildAttr'
import CKEditor from '@ckeditor/ckeditor5-react';
import DocumentEditor from '@ckeditor/ckeditor5-build-classic';
import CollectionApp from "./CollectionApp"
import {isEmpty} from "../component/isEmpty"
import FormSelect from "./FormSelect"
import Parser from "react-html-parser"
import SimpleArray from "./SimpleArray"
import FormExpandedSelect from "./FormExpandedSelect"
import FormAutoSuggest from './FormAutoSuggest'

export default function Widget(props) {
    const {
        form,
        functions,
    } = props

    let wrapper_attr = wrapperAttr(form, 'flex-1 relative')
    let element = 'form element ' + form.type
    let widget_attr = widgetAttr(form, 'w-full', functions)
    if (form.value === null)
        delete form.value

    var errors = []
    if (form.errors.length > 0) {
        wrapper_attr.className += ' errors'
        errors = form.errors.map((content, errorKey) => {
            return (<li key={errorKey}>{content}</li>)
        })
    }

    if (form.type === 'ckeditor') {
        if (typeof form.value === 'undefined' || form.value === null)
            form.value = ''
        return (
            <div {...wrapper_attr}>
                <CKEditor editor={DocumentEditor} data={form.value} aria-describedby={form.id + '_help'} onChange={(event, editor) => functions.onCKEditorChange(event, editor, form)} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'submit') {
        widget_attr.type = 'button'
        widget_attr.style = {float: 'right'}
        widget_attr.className = 'btn-gibbon'
        widget_attr.onClick = (e) => functions.submitForm(e,form)
        return (
            <div {...wrapper_attr}>
                <span className={'emphasis small'}>{form.help}</span>
                <button {...widget_attr}>{Parser(form.label)}</button>
            </div>
        )
    }

    if (form.type === 'button') {
        widget_attr.type = 'button'
        widget_attr.style = {float: 'right'}
        widget_attr.className = 'btn-gibbon'
        return (
            <div {...wrapper_attr}>
                <button {...widget_attr}>{Parser(form.label)}</button>
            </div>
        )
    }

    if (form.type === 'hidden') {
        widget_attr.type = 'hidden'
        return (<input {...widget_attr} />)
    }

    if (form.type === 'email') {
        widget_attr.type = 'email'
        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} defaultValue={form.value} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'password_generator') {
        widget_attr.type = 'password'
        let button_attr = {}

        if (form.name === 'second') {
            return (
                <div {...wrapper_attr}>
                    <input {...widget_attr} defaultValue={form.value} />
                    {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
                </div>
            )
        }
        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} defaultValue={form.value} />
                <button type={'button'} title={form.generateButton.title} className={form.generateButton.class} {...button_attr} onClick={() => functions[form.generateButton.onClick](form)}>{form.generateButton.title}</button>
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'password') {
        widget_attr.type = 'password'
        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} defaultValue={form.value} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'url') {
        widget_attr.type = 'url'
        let button_attr = {}
        if (isEmpty(form.value)) {
            button_attr.disabled = true
        }

        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} defaultValue={form.value} />
                <button type={'button'} title={functions.translate('Open Link')} className={'button button-right'} {...button_attr} onClick={() => functions.openUrl(form.value)}><span className={'fa-fw fas fa-external-link-alt'}></span></button>
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'file') {
        widget_attr.type = 'file'
        let button_attr = {}
        if (isEmpty(form.value)) {
            button_attr.disabled = true
        }
        if (typeof form.photo === 'object' && form.photo.exists) {
            let item = form.photo

            return (
                <div className={'flex-1'}>
                    <div className={'float-left text-center'}>
                        <img src={item.url} title={item.title} className={item.className} key={'photo'} />
                        <div className={'w-2/3 float-right'}>
                            <input {...widget_attr} />
                            <div className={'button-right'}>
                                <button type={'button'} title={functions.translate('File Download')} className={'button'} {...button_attr} onClick={() => functions.downloadFile(form)}><span className={'fa-fw fas fa-file-download'}></span></button>
                                <button type={'button'} title={functions.translate('File Delete')} className={'button'} {...button_attr} onClick={() => functions.deleteFile(form)}><span className={'fa-fw fas fa-trash'}></span></button>
                            </div>
                            {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
                        </div>
                    </div>
                </div>

            )

        }
        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} />
                <div className={'button-right'}>
                    <button type={'button'} title={functions.translate('File Download')} className={'button'} {...button_attr} onClick={() => functions.downloadFile(form)}><span className={'fa-fw fas fa-file-download'}></span></button>
                    <button type={'button'} title={functions.translate('File Delete')} className={'button'} {...button_attr} onClick={() => functions.deleteFile(form)}><span className={'fa-fw fas fa-trash'}></span></button>
                </div>
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'date') {
        let value = form.value
        if (typeof value === 'undefined')
            value = ''
        if (form.value !== null && typeof form.value === 'object') {
            if (typeof form.value.year !== "undefined")
                value = ('0000' + form.value.year).slice(-4) + '-' + ('00' + form.value.month).slice(-2) + '-' + ('00' + form.value.day).slice(-2)
            else if (typeof form.value.date !== "undefined")
                value = form.value.date.toString().slice(0,10)
        }
        value = value.slice(0,10)
        widget_attr.type = 'date'
        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} defaultValue={value} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'time') {
        let value = form.value
        if (form.value !== null && typeof form.value === 'object') {
            console.log(value)
        }
        value = value.slice(0,8)
        widget_attr.type = 'time'
        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} defaultValue={value} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'text') {
        widget_attr.type = 'text'
        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} value={form.value} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'color') {
        if (typeof form.value === 'undefined')
            form.value = ''
         if (/([0-9A-F]{3}){1,2}/i.test(form.value) && form.value.charAt(0) !== '#') {
             form.value = '#' + form.value
         }
        widget_attr.type = 'text'
        return (
            <div {...wrapper_attr} style={{backgroundColor: form.value, padding: '0 0 0 50px'}}>
               <input {...widget_attr} defaultValue={form.value} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'collection') {
        if (typeof form.children === 'undefined')
            form.children = []
        return (<CollectionApp form={form} functions={functions} key={form.collection_key} />)
    }

    if (form.type === 'auto_suggest') {
        return (<FormAutoSuggest form={form} wrapper_attr={wrapper_attr} widget_attr={widget_attr} errors={errors} functions={functions} />)
    }

    if (form.type === 'choice') {
        return (<FormSelect form={form} wrapper_attr={wrapper_attr} widget_attr={widget_attr} errors={errors} functions={functions} />)
    }

    if (form.type === 'expanded_choice') {
        return (<FormExpandedSelect form={form} wrapper_attr={wrapper_attr} widget_attr={widget_attr} errors={errors} functions={functions} />)
    }

    if (form.type === 'textarea') {
        return (
            <div {...wrapper_attr}>
                <textarea {...widget_attr} defaultValue={form.value} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'toggle') {
        widget_attr.type = 'hidden'
        wrapper_attr.className += ' w-full right'
        let button_attr = {}
        button_attr.onClick = (e) => functions.onElementChange(e, form)
        button_attr.className = 'button'
        delete widget_attr.onChange
        let span_attr = {}
        span_attr.className = 'fa-fw far fa-thumbs-down'
        if (form.value === 'Y' || form.value === '1') {
            span_attr.className = 'fa-fw far fa-thumbs-up'
            button_attr.className += ' success'
            form.value = 'Y'
        } else {
            form.value = 'N'
        }

        return (
            <div {...wrapper_attr}>
                <input {...widget_attr}  defaultValue={form.value} />
                <button type={'button'} title={functions.translate('Yes/No')} {...button_attr}><span {...span_attr}></span></button>
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'number') {
        widget_attr.type = 'number'
        return (
            <div {...wrapper_attr}>
                <input {...widget_attr} defaultValue={form.value} />
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'display') {
        return (
            <div {...wrapper_attr}>
                {form.value}
                {form.errors.length > 0 ? <ul>{errors}</ul> : ''}
            </div>
        )
    }

    if (form.type === 'simple_array') {
        return (
            <SimpleArray wrapper_attr={wrapper_attr} form={form} functions={functions} errors={form.errors} />
        )
    }

    console.log(form)
    return (<div {...wrapper_attr}>
        {element}
    </div>)
}

Widget.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}