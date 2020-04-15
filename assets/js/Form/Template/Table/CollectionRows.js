'use strict'

import React from "react"
import PropTypes from 'prop-types'
import {widgetAttr} from "../../buildAttr"
import CollectionHeaderRow from "./CollectionHeaderRow"
import RowTemplate from "./Row"
import Widget from "../../Widget"
import Messages from "../../../component/Messages"

export default function CollectionRows(props) {
    const {
        form,
        functions,
        columnCount,
    } = props

    let hidden = []
    let hiddenKey = 0

    let cCount = 0
    if (typeof form.header_row !== "boolean")
        cCount = Object.keys(form.header_row).length
    else
        cCount = columnCount

    let table_attr = widgetAttr(form, 'w-full leftIndent smallIntBorder standardForm striped', {})
    delete table_attr.name
    let errors = form.errors
    const header = (<CollectionHeaderRow
        form={form}
        functions={functions} />
        )

    let rows = []
    if (Object.keys(errors).length > 0) {
        rows.push(<tr key={'errors'}><td colSpan={cCount}><div className={'errors flex-1 relative'}>{errors}</div></td></tr>)
    }

    if (typeof form.children !== "undefined" && Object.keys(form.children).length > 0) {
        Object.keys(form.children).map(rowKey => {
            let row = form.children[rowKey]
            let columns = []
            if (typeof row.children === 'undefined') {
                if (row.type !== 'hidden') {
                    columns.push(<RowTemplate form={{...row}} functions={functions} columns={cCount}/>)
                } else {
                    hidden.push(<Widget form={{...row}} functions={functions} key={hiddenKey++}/>)
                }

            } else {
                Object.keys(row.children).map(childKey => {
                    let child = row.children[childKey]
                    if (child.type !== 'hidden') {
                        columns.push(<td key={childKey} className={'w-1/' + cCount}><Widget form={{...child}} functions={functions}/></td>)
                    } else {
                        hidden.push(<Widget form={{...child}} functions={functions} key={hiddenKey++}/>)
                    }
                })
            }

            let buttons = []
            if (form.allow_delete) {
                buttons.push(<a title={functions.translate('Delete')} onClick={() => functions.deleteElement(row)} className={'delete-button'} key={'delete'}><span className={'far fa-trash-alt fa-fw'}></span></a>)
            }

            if (buttons.length > 0) {
                columns.push(<td key={'actions'}>
                    <div className={'text-center'}>{buttons}</div>
                </td>)
            }

            rows.push(<tr key={rowKey}>{columns}</tr>)
        })
    } else {
        rows.push(<tr key={'emptyWarning'}>
            <td colSpan={cCount}>
                <div className={'warning'}>{functions.translate('There are no records to display.')}</div>
            </td>
        </tr>)
    }

    if (form.allow_add) {
        rows.push(<tr key={'addRow'} className={'collectionAdd'}>
            <td colSpan={cCount - 1}>&nbsp;</td>
            <td>
                <div className={'text-center'}>
                    <a title={functions.translate('Add')} onClick={() => functions.addElement(form)}
                            className={'add-button'} key={'add'}><span
                        className={'fas fa-plus-circle fa-fw'}></span></a>
                </div>
            </td>
        </tr>)
    }

    return (
        <div className={'collection'}>
            <Messages messages={errors} translate={functions.translate} />
            <table {...table_attr}>
                {header}
                <tbody>
                    {rows}
                </tbody>
            </table>
            <div className={'hidden'}>{hidden}</div>
        </div>)

}

CollectionRows.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    columnCount: PropTypes.number.isRequired,
}

CollectionRows.defaultProps = {
    errors: [],
}