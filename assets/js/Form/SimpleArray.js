'use strict'

import React from "react"
import PropTypes from 'prop-types'
import {widgetAttr} from "./buildAttr"

export default function SimpleArray(props) {
    const {
        form,
        wrapper_attr,
        errors,
        functions,
    } = props

    let children = []
    Object.keys(form.children).map(key => {
        let child = form.children[key]
        children.push(child)
    })
    form.children = children

    children = form.children.map((child,key) => {
        let widget_attr = widgetAttr(child, 'w-full', functions)
        widget_attr.type = 'text'
        delete widget_attr.onClick
        widget_attr['aria-describedby'] = form.id + '_help'

        return (<div key={key}>
            <input {...widget_attr} value={child.value} />
            <div className={'button-right'}>
                <button type={'button'} title={functions.translate('Delete')} className={'button'} onClick={() => functions.removeSimpleArrayValue(child,form)}><span className={'fa-fw far fa-trash-alt'}></span></button>
            </div>
        </div>)
    })


    let widget_attr = {}
    widget_attr.type = 'text'
    widget_attr.className = 'w-full'
    widget_attr.name = form.fullName + '[' + children.length + ']'
    widget_attr.id = form.id + '_' + children.length

    children.push(<div key={children.length}>
        <div className={'button-right'}>
            <button type={'button'} title={functions.translate('Add')} className={'button'} onClick={() => functions.addSimpleArrayValue(form)}><span className={'fa-fw far fa-plus-square'}></span></button>
        </div>
    </div>)

    return (
        <div {...wrapper_attr}>
            {children}
            {errors}
        </div>
    )
}

SimpleArray.propTypes = {
    form: PropTypes.object.isRequired,
    wrapper_attr: PropTypes.object.isRequired,
    errors: PropTypes.array,
    functions: PropTypes.object.isRequired,
}

SimpleArray.defaultProps = {
    errors: [],
}