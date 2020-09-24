'use strict'

import React from "react"
import PropTypes from 'prop-types'
import Img from 'react-image'
import Parser from "react-html-parser"
import { isEmpty } from '../component/isEmpty'
import PaginationGroup from './PaginationGroup'
import ContentRow from './ContentRow'

export default function PaginationContent(props) {
    const {
        row,
        group,
        content,
        functions,
        draggableSort,
    } = props

    function dropItem(e) {
        let parent = e.target.parentNode
        parent.classList.remove('dropTarget')
        let data = document.getElementById(e.dataTransfer.getData("text"))
        data.classList.remove('dropTarget')
        data.classList.remove('bg-green-200')
        let x = document.getElementsByClassName('bg-green-200')
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove('bg-green-200')
        }

        return functions.dropEvent(e)
    }

    if (content.length === 0)
    {
        return (
            <tbody>
                <tr>
                    <td colSpan={row.columns.length + 1}>
                        <div className="h-48 rounded-sm border bg-gray-100 shadow-inner overflow-hidden">
                            <div className="w-full h-full flex flex-col items-center justify-center text-gray-600 text-lg">
                                {row.emptyContent}
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>)
    }
    if (group.name !== '') {
        return (<tbody><PaginationGroup {...props} /></tbody>)
    }

    let rows = Object.keys(content).map(rowKey => {
        return (<ContentRow {...props} rowKey={rowKey} key={rowKey} />)
    })

    if (draggableSort) {
        return (
            <tbody onDrop={(e) => dropItem(e)}>
            {rows}
            </tbody>)
    }

    return (<tbody>
        {rows}</tbody>)
}


PaginationContent.propTypes = {
    row: PropTypes.object.isRequired,
    group: PropTypes.object.isRequired,
    content: PropTypes.array.isRequired,
    functions: PropTypes.object.isRequired,
    draggableSort: PropTypes.bool.isRequired,
}
