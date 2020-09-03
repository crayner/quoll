'use strict'

import React from "react"
import PropTypes from 'prop-types'
import ContentRow from './ContentRow'
import HeaderRow from './HeaderRow'

export default function PaginationGroup(props) {
    const {
        row,
        group,
        content,
        functions,
        draggableSort,
    } = props

    let result = []
    let groupName = '';

    let loop = 0
    Object.keys(content).map(rowKey => {
        let rowContent = content[rowKey]
        if (groupName !== rowContent[group.contentKey]) {
            groupName = rowContent[group.contentKey]
            result.push(<tr key={loop++} className={'head-light'}>
                <th colSpan={row.columns.length + 1} className={'head-light'}>
                    <h3 className={'sub-pagination-header'}>{groupName}</h3>
                </th>
            </tr>)
            result.push(<HeaderRow row={row} sortColumn={functions.sortColumn} sortColumnName={''}
                                                       sortColumnDirection={''} key={loop++}/>)
        }

        result.push(<ContentRow {...props} rowKey={rowKey} key={loop++} />)
    })

    return(result)
}

PaginationGroup.propTypes = {
    row: PropTypes.object.isRequired,
    group: PropTypes.object.isRequired,
    content: PropTypes.array.isRequired,
    functions: PropTypes.object.isRequired,
    draggableSort: PropTypes.bool.isRequired,
}

