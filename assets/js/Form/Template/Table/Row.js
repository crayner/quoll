'use strict'

import React from "react"
import PropTypes from 'prop-types'
import HeaderRow from "./HeaderRow"
import ParagraphRow from "./ParagraphRow"
import SingleRow from "./SingleRow"
import Widget from "../../Widget"
import Standard from "./Standard"
import AttendanceRollGroupChangeAll from '../../Special/AttendanceRollGroupChangeAll'
import AttendanceSummary from '../../Special/AttendanceSummary'

export default function Row(props) {
    const {
        form,
        functions,
        columns,
        visibleKeys
    } = props


    if (form.visible_values.length > 0 && Object.keys(visibleKeys).length > 0) {
        let visible = false
        form.visible_values.map(name => {
            let key = form.visible_parent + '_' + name
            if (typeof visibleKeys[key] === 'undefined') {
                console.log(visibleKeys)
                console.error('The key "' + key + '" does not exist in visible keys.  Please ensure that it exists in the Symfony form type for ' + form.full_name)
            } else {
                if (visible === false) {
                    let value = true
                    if (typeof visibleKeys[key] !== 'undefined') {
                        value = visibleKeys[key]
                    }
                    if (value === true) {
                        visible = true
                        if (Object.keys(form.visible_labels).length > 0) {
                            form.label = form.visible_labels[name].label
                            form.help = form.visible_labels[name].help
                        }
                    }
                }
            }
        })
        if (visible === false) {
            return (<tr className={'hidden'}><td><Widget form={form} functions={functions} columns={columns} /></td></tr>)
        }
    }

    form.columns = columns
    if (form.type === 'hidden' && form.row_style !== 'hidden') form.row_style = 'hidden'

    if (form.type === 'header') {
        return (<HeaderRow form={form} functions={functions} columns={columns}/>)
    }

    if (form.type === 'paragraph') {
        return (<ParagraphRow form={form} functions={functions} columns={columns}/>)
    }

    if (form.type === 'transparent') {
        return Object.keys(form.children).map(name => {
            let child = form.children[name]
            return (<Row form={child} key={child.name} functions={functions} columns={columns} visibleKeys={visibleKeys}/>)
        })
    }

    if (form.row_style === 'single' || form.type === 'submit') {
        return (<SingleRow form={form} functions={functions} columns={columns}/>)
    }

    if (form.row_style === 'hidden') {
        return (<tr className={'hidden'}><td><Widget form={form} functions={functions} columns={columns} /></td></tr>)
    }

    if (form.row_style === 'standard') {
        return (<Standard form={form} functions={functions} columns={columns} />)
    }

    if (form.row_style === 'multiple_widget') {
        return (<Standard form={form} functions={functions} columns={columns} />)
    }

    if (form.row_style === 'transparent' || form.row_style === 'repeated')
    {
        if (form.type === 'collection' && typeof form.children === 'undefined') {
            return ([])
        }
        if (form.type === 'collection') {
            Object.keys(form.children).map(childKey => {
                let child = form.children[childKey]
                return (<Widget form={child} functions={functions} columns={columns} />)
            })
        }

        return Object.keys(form.children).map(childKey => {
            let child = form.children[childKey]
            if (child.type === 'password_generator' && childKey === 'second') {
                child.type = 'password'
            }
            return (<Row form={child} key={child.name} functions={functions} columns={columns} visibleKeys={visibleKeys}/>)
        })
    }

    if (form.row_style === 'simple_array') {
        return (<Standard form={form} functions={functions} columns={columns} />)
    }


    if (form.row_style === 'special') {
        if (form.special_name === false) {
            console.log(form)
            console.error("The form has set a style of special without setting the special_name in the options.", form.row_style)
            return (<tr><td> Form Row {form.row_style}</td></tr>)
        }
        if (form.special_name === 'AttendanceRollGroupChangeAll') {
            return (<AttendanceRollGroupChangeAll form={form} functions={functions} />)
        }
        if (form.special_name === 'AttendanceSummary') {
            return (<AttendanceSummary form={form} functions={functions} />)
        }

        console.log(form)
        console.error("The form has set a style of special but the name given has not been coded in REACT." , form.special_name)
        return (<tr><td> Form Row {form.special_name}</td></tr>)

    }


    console.log(form)
    console.error("The form has an unknown row style. ", form.row_style)
    return (<tr><td> Form Row {form.row_style}</td></tr>)

}

Row.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    columns: PropTypes.number.isRequired,
    visibleKeys: PropTypes.object.isRequired,
}