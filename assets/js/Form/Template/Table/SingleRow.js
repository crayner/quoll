'use strict'

import React from "react"
import PropTypes from 'prop-types'
import {rowAttr, columnAttr} from '../../buildAttr'
import Widget from "../../Widget"

export default function SingleRow(props) {
    const {
        form,
        functions,
        columns,
    } = props

    if (form.column_attr === false && columns > 1) form.column_attr = {}
    if (columns > 1) form.column_attr.colSpan = columns

    let row_class = 'flex flex-col sm:flex-row justify-between content-center p-0'
    if (form.row_style === 'hidden') {
        row_class = 'flex flex-col sm:flex-row justify-between content-center p-0 hidden'
    }

    let row_attr = rowAttr(form, row_class)
    let column_attr = columnAttr(form, 'flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0')

    return (<tr {...row_attr}>
        <td {...column_attr}>
            <Widget form={form} functions={functions} />
        </td>
    </tr>)

}

SingleRow.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    columns: PropTypes.number.isRequired,
}