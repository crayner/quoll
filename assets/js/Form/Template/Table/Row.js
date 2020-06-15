'use strict'

import React from "react"
import PropTypes from 'prop-types'
import HeaderRow from "./HeaderRow"
import ParagraphRow from "./ParagraphRow"
import SingleRow from "./SingleRow"
import Widget from "../../Widget"
import Standard from "./Standard"

export default function Row(props) {
    const {
        form,
        functions,
        columns,
        visibleKeys
    } = props


    if (form.visible_values.length > 0) {
        let visible = false
        form.visible_values.map(name => {
            let key = form.visible_parent + '_' + name
            if (visible === false) {
                let value = true
                if (typeof visibleKeys[key] !== 'undefined') {
                    value = visibleKeys[key]
                }
                if (value === true) {
                    visible = true
                }
            }
        })
        if (visible === false) {
            return (<tr className={'hidden'}><td><Widget form={form} functions={functions} /></td></tr>)
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
        return (<tr className={'hidden'}><td><Widget form={form} functions={functions} /></td></tr>)
    }

    if (form.row_style === 'standard') {
        return (<Standard form={form} functions={functions} />)
    }

    if (form.row_style === 'multiple_widget') {
        return (<Standard form={form} functions={functions} />)
    }

    if (form.row_style === 'transparent' || form.row_style === 'repeated')
    {
        if (form.type === 'collection') {
            Object.keys(form.children).map(childKey => {
                let child = form.children[childKey]
                return (<Widget form={child} functions={functions} />)
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
        return (<Standard form={form} functions={functions} />)
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