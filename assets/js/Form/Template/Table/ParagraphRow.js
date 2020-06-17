'use strict'

import React from "react"
import PropTypes from 'prop-types'
import {rowAttr, columnAttr, wrapperAttr} from '../../buildAttr'
import Parser from "html-react-parser"

export default function ParagraphRow(props) {
    const {
        form,
        functions,
        columns,
        noWrapper
    } = props

    let wrapper_attr = wrapperAttr(form, '')
    if (noWrapper) {
        return (
            <div {...wrapper_attr}>
                {Parser(form.help)}
            </div>
        )
    }

    let row_attr = rowAttr(form, 'break flex flex-col sm:flex-row justify-between content-center p-0')
    let column_attr = columnAttr(form, 'flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0')
    if (columns > 1) {
        column_attr.colSpan = columns
    }

    if (form.row_style === 'hidden') {
        return ''
    }

    return (<tr {...row_attr} style={form.style}>
        <td {...column_attr}>
            <div {...wrapper_attr}>
                {Parser(form.help)}
            </div>
        </td>
    </tr>)

}

ParagraphRow.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    columns: PropTypes.number.isRequired,
    noWrapper: PropTypes.bool,
}

ParagraphRow.defaultValue = {
    noWrapper: false
}