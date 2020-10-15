'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function HeaderRow(props) {
    const {
        row,
        sortColumn,
        sortColumnName,
        sortColumnDirection,
        functions,
        selectAllRows
    } = props

    let columns = []
    Object.keys(row.columns).map(columnKey => {
        const column = row.columns[columnKey]
        if (column.dataOnly)
            return
        const help = column.help !== null ? (<span className={'small text-gray-600 italic'}><br/>{column.help}</span>) : ''
        let sort = column.sort === true ? (<span className={'fas fa-sort fa-fw text-gray-600'} style={{float: 'right'}}/>) : ''
        let search = column.search === true ? (<span className={'fas fa-search fa-fw text-gray-800'} style={{float: 'right'}}/>) : ''
        let w = sortColumnName
        if (typeof sortColumnName === 'array')
            w = sortColumnName[0]
        if (column.contentKey === null) {
            console.error('The columnKey MUST be set in the pagination column: ' + column.label)
        }
        if (column.contentKey.includes(w)) {
            sort = (<span className={'fas fa-sort-' + sortColumnDirection + ' fa-fw text-gray-800'} style={{float: 'right'}}/>)
        }

        let headerClass = column.headerClass !== '' ? column.headerClass : column.class

        columns.push(<th className={headerClass} key={columnKey} onClick={() => sortColumn(column.contentKey)}>{search}{sort}{column.label}{help}</th>)
    })

    let selectRow = null
    if (row.selectRow) {
        selectRow = (<div className={'pl-1'}><input type={'checkBox'} checked={selectAllRows} onChange={() => functions.toggleAll()} /></div>)
    }

    if (row.actions.length > 0) {
        columns.push(<th className={'column relative width1'} key={'actions'}>
            <div className={'flex'}>
                {row.actionTitle}{selectRow}
            </div>
        </th>)
    }

    return (<tr className={'head text-xs head-dark'}>{columns}</tr>)
}


HeaderRow.propTypes = {
    row: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    sortColumn: PropTypes.func.isRequired,
    sortColumnName: PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.array,
    ]).isRequired,
    sortColumnDirection: PropTypes.string.isRequired,
    selectAllRows: PropTypes.bool.isRequired,
}
