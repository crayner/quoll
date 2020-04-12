'use strict'

import React from "react"
import PropTypes from 'prop-types'
import {rowAttr, columnAttr} from '../../buildAttr'
import Parser from "html-react-parser"

export default function HeaderRow(props) {
    const {
        form,
        functions,
        columns,
    } = props

    let row_attr = rowAttr(form, 'break flex flex-col sm:flex-row justify-between content-center p-0')
    let column_attr = columnAttr(form, 'flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0')
    if (columns > 1) {
        column_attr.colSpan = columns
    }
    let label = (<h3 {...form.label_attr}>{form.label}</h3>)
    if (form.header_type === 'h4')
        label = (<h4 {...form.label_attr}>{form.label}</h4>)
    if (form.header_type === 'h5')
        label = (<h5 {...form.label_attr}>{form.label}</h5>)
    if (form.header_type === 'h6')
        label = (<h6 {...form.label_attr}>{form.label}</h6>)

    let help = ''
    if (typeof form.help === 'string')
        help = (<div {...form.help_attr}>{Parser(form.help)}</div>)

    return (<tr {...row_attr}>
        <td {...column_attr}>
            {label}
            {help}
        </td>
    </tr>)

}

HeaderRow.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    columns: PropTypes.number.isRequired,
}