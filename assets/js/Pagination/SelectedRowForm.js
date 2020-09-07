'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function SelectedRowForm(props) {
    const {
        functions,
        row,
        messages
    } = props

    let action = null
    Object.keys(row.actions).map(index => {
        let x = row.actions[index]
        if (x.selectRow) {
            action = x
        }
    })

    let loop = 0
    let options = Object.keys(action.selectActions).map(index => {
        let x = action.selectActions[index]
        return (<option value={x.route} key={loop++}>{x.prompt}</option> )
    })

    options.unshift(<option key={loop++}>{messages['Select action...']}</option>)

    return (<tr className={'flex flex-col sm:flex-row justify-between content-center p-0'}>
        <td className="flex flex-col flex-grow justify-center -mb-1 sm:mb-0 px-2 border-b-0 sm:border-b border-t-0">
            <label htmlFor="select_row_action">{messages['Selected Row Action']}</label>
        </td>
        <td className={'w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0'}>
            <div className={'flex-1 relative'}>
                <select id={'select_row_action'} onChange={(e) => functions.manageSelectedRowAction(e)} className={'w-full'} >{options}</select>
            </div>
        </td>
    </tr>)

}

SelectedRowForm.propTypes = {
    functions: PropTypes.object.isRequired,
    row: PropTypes.object.isRequired,
    messages: PropTypes.object.isRequired,
}


