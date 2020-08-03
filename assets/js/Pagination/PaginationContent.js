'use strict'

import React from "react"
import PropTypes from 'prop-types'
import Img from 'react-image'
import Parser from "react-html-parser"
import { isEmpty } from '../component/isEmpty'

export default function PaginationContent(props) {
    const {
        row,
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

    let rows = Object.keys(content).map(rowKey => {
        const rowContent = content[rowKey]
        let columns = []
        Object.keys(row.columns).map(columnKey => {
            let columnDefinition = row.columns[columnKey]
            if (!isEmpty(columnDefinition.defaultValue)) {
                columnDefinition.contentKey.map((name,key) => {
                    if (isEmpty(rowContent[name]) && !isEmpty(columnDefinition.defaultValue[key])) {
                        rowContent[name] = columnDefinition.defaultValue[key]
                    }
                })
            }
            if (columnDefinition.dataOnly)
                return
            let columnContent = []
            if (typeof columnDefinition.contentKey === 'object')
            {
                if (columnDefinition.contentType === 'image') {
                    let style = typeof columnDefinition.options['style'] === 'undefined' ? {} : columnDefinition.options['style']
                    let className = typeof columnDefinition.options['class'] === 'undefined' ? '' : columnDefinition.options['class']
                    columnDefinition.contentKey.map((value, key) => {
                        if (key === 0) {
                            let url = rowContent[value]
                            if (!url.includes('http')) {
                                if (url[0] !== '/') {
                                    url = '/' + url
                                }
                                let host = window.location.protocol + '//' + window.location.hostname
                                url = host + url
                            }
                            columnContent.push(<Img src={url} style={style} className={className}
                                                    key={key}/>)
                        }
                    })
                } else if (columnDefinition.contentType === 'link') {
                    let link = typeof columnDefinition.options['link'] === 'undefined' ? '#' : columnDefinition.options['link']
                    let title = typeof columnDefinition.options['title'] === 'undefined' ? '' : columnDefinition.options['title']
                    let style = typeof columnDefinition.options['style'] === 'undefined' ? {} : columnDefinition.options['style']
                    let className = typeof columnDefinition.options['class'] === 'undefined' ? '' : columnDefinition.options['class']

                    if (typeof columnDefinition.options['route_options'] === 'object') {
                        Object.keys(columnDefinition.options['route_options']).map(x => {
                            const search = columnDefinition.options['route_options'][x]
                            link = link.replace('__' + search + '__', rowContent[search])
                        })
                    }

                    columnDefinition.contentKey.map((value, key) => {
                        if (key === 0)
                            if (className === '')
                                columnContent.push(<a href={link} title={title} style={style} key={key}>{Parser(rowContent[value])}</a>)
                            else
                                columnContent.push(<a href={link} title={title} className={className} style={style} key={key}>{Parser(rowContent[value])}</a>)
                    })
                } else {
                    columnDefinition.contentKey.map((value, key) => {
                        if (key > 0)
                            columnContent.push(<span key={key}
                                                     className={'small text-gray-600 italic'}><br/>{Parser(columnDefinition.translate ? functions.translate(rowContent[value]) : rowContent[value])}</span>)
                        else
                            columnContent.push(<span key={key}>{Parser(columnDefinition.translate ? functions.translate(rowContent[value]) : rowContent[value])}</span>)
                    })
                }
            } else {
                console.log(columnDefinition)
                columnContent = columnDefinition.translate ? functions.translate([rowContent[columnDefinition.contentKey]]) : [rowContent[columnDefinition.contentKey]]
            }

            columns.push(<td key={columnKey} className={columnDefinition.class}>{columnContent}</td> )
        })
        // add Actions column
        let actions = Object.keys(row.actions).map(actionKey => {
            let action = row.actions[actionKey]
            rowContent.options = action.options
            if (action.displayWhen === '' || rowContent[action.displayWhen] === true || rowContent[action.displayWhen] === 'Y') {
                if (action.onClick === '') {
                    return (
                        <a onClick={() => functions.getContent(rowContent.actions[actionKey])} className={action.aClass}
                           key={actionKey}
                           title={action.title}><span className={action.spanClass} /></a>)
                }
                if (action.onClick === false) {
                    return (
                        <a href={rowContent.actions[actionKey].url} className={action.aClass}
                           key={actionKey}
                           title={action.title}><span className={action.spanClass} /></a>)
                }

                return (<a onClick={() => functions[action.onClick](rowContent.actions[actionKey],rowContent)}
                           className={action.aClass} key={actionKey} title={action.title}>
                    <span className={action.spanClass} /></a>)
            }
        })
        if (row.actions.length > 0) {
            columns.push(<td key={'actions'}>
                <div
                    className="float-right group-hover:flex sm:flex">
                    {actions}
                </div>
            </td>)
        }

        if (typeof rowContent.id === 'undefined') {
            console.log(rowContent)
            console.error('You must define an "id" in your pagination array.')
        }

        let rowClassName = ''
        if (row.highlight !== false) {
            let highlights = {...row.highlight}
            Object.keys(highlights).map(key => {
                let highlight = highlights[key]
                if (rowContent[highlight.columnKey] === highlight.columnValue) {
                    rowClassName = rowClassName + ' ' + highlight.className
                }
            })
        }

        if (draggableSort) {
            return (
                <tr className={rowClassName} key={rowKey} id={'pagination' + rowContent.id} draggable="true" onDragStart={(e) => drag(e)} onDragOver={(e) => allowDrop(e)} onDragEnter={(e) => toggleColour(e, true)} onDragLeave={(e) => toggleColour(e,false)}>{columns}</tr>)
        }

        return (<tr className={rowClassName} key={rowKey} id={'pagination' + rowContent.id}>{columns}</tr>)

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
    content: PropTypes.array.isRequired,
    functions: PropTypes.object.isRequired,
    draggableSort: PropTypes.bool.isRequired,
}

function toggleColour(e, on) {
    e.preventDefault()
    if (on && e.target.parentNode.classList.contains('dropTarget'))
        return
    if (!on && !e.target.parentNode.classList.contains('dropTarget'))
        return
    e.target.parentNode.classList.toggle('dropTarget')
    e.target.parentNode.classList.toggle('bg-green-200')
}

function allowDrop(e) {
    e.preventDefault()
}

function drag(e) {
    e.dataTransfer.setData('text', e.target.id)
}
