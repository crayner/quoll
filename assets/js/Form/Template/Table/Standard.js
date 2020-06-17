'use strict'

import React from "react"
import PropTypes from 'prop-types'
import LabelHelp from "../../LabelHelp"
import Widget from "../../Widget"
import {columnAttr, rowAttr} from "../../buildAttr"

export default function Standard(props) {
    const {
        form,
        functions,
        columns,
    } = props

    let row_attr = rowAttr(form, 'flex flex-col sm:flex-row justify-between content-center p-0')
    let column_attr = columnAttr(form, 'w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0')

    if (form.row_style === 'multiple_widget') {
        const widgets = Object.keys(form.children).map(key => {
            const child = form.children[key]
            return (<Widget key={key} form={child} functions={functions} columns={columns} />)
        })
        return (<tr {...row_attr}>
            <td className={'flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0'}>
                <LabelHelp form={form}/>
            </td>
            <td {...column_attr}>
                {widgets}
            </td>
        </tr>)
    }

    return (<tr {...row_attr}>
        <td className={'flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0'}>
            <LabelHelp form={form}/>
        </td>
        <td {...column_attr}>
            <Widget form={form} functions={functions} columns={columns} />
        </td>
    </tr>)
}

Standard.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    columns: PropTypes.number.isRequired,
}