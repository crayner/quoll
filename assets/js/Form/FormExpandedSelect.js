'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function FormExpandedSelect(props) {
    const {
        form,
        wrapper_attr,
        widget_attr,
        errors,
        functions,
    } = props

    function onElementChange(e,child,parent)
    {
        let value = parent.value
        if (e.target.checked) {
            // turn the value on in the array by adding the value
            value.push(child.name)
            value = [...new Set(value)]
        } else {
            // turn the value off in the array by removal of the value
            value.splice( value.indexOf(child.name), 1 );
        }
        let event = {}
        event.target = {}
        event.target.value = value
        functions.onElementChange(event,parent)
    }

    function isEmpty(obj) {
        for(var key in obj) {
            if(obj.hasOwnProperty(key))
                return false;
        }
        return true;
    }

    function toggleAll(parent)
    {
        const all = functions.toggleExpandedAllNone(parent.id, true)
        let event = {}
        event.target = {}
        event.target.value = []

        if (all) {
            event.target.value = parent.choices.map(choice => {
                return choice.value
            })
        }
        functions.onElementChange(event,parent)
    }

    let rr = '_' + Math.random().toString(36).substr(2, 9);


    if (typeof form.children === 'undefined')
        form.children = []

    let options = Object.keys(form.children).map(key => {
        const child = form.children[key]
        let name = child.full_name.replace('[]', '[' + child.name + ']')
        let checked = false
        if (form.value.length > 0 && form.value.includes(child.name))
            checked = true
        return (<label key={key + rr} htmlFor={child.id}>{child.label} <input type={'checkbox'} id={child.id} name={name} checked={checked} onChange={(e) => onElementChange(e,child,form)} /><br/></label>)
    })

    let id = form.id + '_0'
    let name = form.full_name + '[0]'

    options.unshift(<label key={'0' + rr} htmlFor={id}>{functions.translate('All / None')} <input type={'checkbox'}
                                                                             id={id} name={name} onChange={() => toggleAll(form)} checked={functions.toggleExpandedAllNone(form.id, false)} /><br/></label>)

    widget_attr.className = widget_attr.className + ' text-right'

    delete widget_attr.onChange

    return (
        <div {...wrapper_attr}>
            <fieldset {...widget_attr}>
                {options}
            </fieldset>
            {errors}
        </div>
    )
}

FormExpandedSelect.propTypes = {
    form: PropTypes.object.isRequired,
    wrapper_attr: PropTypes.object.isRequired,
    widget_attr: PropTypes.object.isRequired,
    errors: PropTypes.array,
    functions: PropTypes.object.isRequired,
}

FormExpandedSelect.defaultProps = {
    errors: [],
}

