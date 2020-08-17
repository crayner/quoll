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
            value.push(e.target.value)
            value = [...new Set(value)]
        } else {
            // turn the value off in the array by removal of the value
            value.splice( value.indexOf(e.target.value), 1 );
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
        if (all === true) {
            event.target.value = parent.children.map(child => {
                return child.value
            })
        }
        functions.onElementChange(event, parent)
    }
    
    function isGrouped(choices)
    {
        let grouped  = false
        Object.keys(choices).map(key => {
            let choice = choices[key]
            if (typeof choice.choices !== 'undefined') {
                grouped = true
            }
        })
        return grouped
    }

    function getOptions() {
        if (typeof form.children === 'undefined')
            form.children = []
        let options = []
        let id = form.id + '_0'
        let name = form.full_name + '[0]'
        options.push(<label key={'AllNone'} htmlFor={id + '_all_none'}>{functions.translate('All / None')}&nbsp;<input type={'checkbox'}
                                                                                                        id={id + '_all_none'}
                                                                                                        name={name}
                                                                                                        onChange={() => toggleAll(form)}
                                                                                                        checked={functions.toggleExpandedAllNone(form.id, false)}/><br/></label>)
        let grouped = isGrouped(form.choices)

        if (grouped) {
            options.push(<hr key={'line'} className={'text-black bg-black border-0'} style={{height: '1px'}} />)
            Object.keys(form.choices).map(w => {
                let group = form.choices[w]
                let subOptions = []
                Object.keys(group.choices).map(choiceKey => {
                    let choice = group.choices[choiceKey]
                    Object.keys(form.children).map(key => {
                        const child = form.children[key]
                        if (child.value === choice.value) {
                            let name = child.full_name.replace('[]', '[' + child.name + ']')
                            let checked = false
                            if (form.value.length > 0 && form.value.includes(choice.value)) {
                                checked = true
                            }
                            subOptions.push(<label key={choice.value} htmlFor={child.id}>{choice.label}&nbsp;<input
                                type={'checkbox'}
                                id={child.id}
                                defaultValue={choice.value}
                                name={name}
                                checked={checked}
                                onChange={(e) => onElementChange(e, child, form)}/><br/></label>)
                        }
                    })
                })
                options.push(<label key={w}><br /><span className={'float-left font-bold'}>{group.label}</span><br />{subOptions}<hr className={'text-black bg-black border-0'} style={{height: '1px'}} /></label> )
            })
            return options
        }


        Object.keys(form.children).map(key => {
            const child = form.children[key]
            const choice = form.choices[key]
            let name = child.full_name.replace('[]', '[' + child.name + ']')
            let checked = false
            if (form.value.length > 0 && form.value.includes(choice.value)) {
                checked = true
            }
            options.push(<label key={choice.value} htmlFor={child.id}>{choice.label}&nbsp;<input type={'checkbox'}
                                                                                                id={child.id}
                                                                                                defaultValue={choice.value}
                                                                                                name={name}
                                                                                                checked={checked}
                                                                                                onChange={(e) => onElementChange(e, child, form)} /><br/></label>)
        })

        return options
    }

    widget_attr.className = widget_attr.className + ' text-right'

    delete widget_attr.onChange

    return (
        <div {...wrapper_attr}>
            <fieldset {...widget_attr}>
                {getOptions()}
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

