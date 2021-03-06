'use strict'

import React from "react"
import PropTypes from 'prop-types'
import Img from 'react-image'
import Parser from "react-html-parser"
import { isEmpty } from '../component/isEmpty'

export default function ContentRow(props) {
    const {
        row,
        content,
        functions,
        draggableSort,
        rowKey,
    } = props

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
                        if (url !== null) {
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
            columnContent = columnDefinition.translate ? functions.translate([rowContent[columnDefinition.contentKey]]) : [rowContent[columnDefinition.contentKey]]
        }

        columns.push(<td key={columnKey} className={columnDefinition.class}>{columnContent}</td> )
    })
    // add Actions column
    let selectAction = null
    let actions = []
    Object.keys(row.actions).map(actionKey => {
        let action = row.actions[actionKey]
        if (!action.selectRow) {
            rowContent.options = action.options
            if (action.displayWhen === '' || rowContent[action.displayWhen] === true || rowContent[action.displayWhen] === 'Y') {
                if (action.onClick === '') {
                    actions.push(<a onClick={() => functions.getContent(rowContent.actions[actionKey])} className={action.aClass}
                           key={actionKey}
                           title={action.title}><span className={action.spanClass}/></a>)
                    return
                }
                if (action.onClick === false) {
                    actions.push(<a href={rowContent.actions[actionKey].url} className={action.aClass}
                           key={actionKey}
                           title={action.title}><span className={action.spanClass}/></a>)
                    return
                }

                actions.push(<a onClick={() => functions[action.onClick](rowContent.actions[actionKey], rowContent)}
                           className={action.aClass} key={actionKey} title={action.title}>
                    <span className={action.spanClass}/></a>)
            } else {
                const spanClass = action.spanClass.replace('text-gray-800','text-transparent').replace(/hover:text-(\w.*)-500/g, 'hover:text-white').trim()
                actions.push(<span title={functions.translate('Disabled')} className={spanClass} key={actionKey} />)
            }
        } else {
            selectAction = {...action}
        }
    })

    let selectedRow = null
    if (row.selectRow) {
        if (typeof rowContent.selected === 'undefined') {
            rowContent.selected = false
        }
        if (rowContent.selected === true) {
            selectedRow = (<a onClick={() => functions.toggleSelectedRow(rowContent)} className={'p-3 sm:p-0'}
                   title={functions.translate('Toggle Selection')}><span className={'far fa-check-circle fa-fw fa-1-5x text-teal-800 hover:text-gray-500'}/></a>)
        } else {
            selectedRow = (<a onClick={() => functions.toggleSelectedRow(rowContent)} className={'p-3 sm:p-0'}
                   title={functions.translate('Toggle Selection')}><span className={'far fa-circle fa-fw fa-1-5x text-gray-800 hover:text-teal-500'}/></a>)
        }
    }

    if (row.actions.length > 0) {
        columns.push(<td key={'actions'} className={'column relative width1'}>
            <div className={'flex'}>
                {actions}{selectedRow}
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

}

ContentRow.propTypes = {
    row: PropTypes.object.isRequired,
    group: PropTypes.object.isRequired,
    content: PropTypes.array.isRequired,
    functions: PropTypes.object.isRequired,
    draggableSort: PropTypes.bool.isRequired,
    rowKey: PropTypes.string.isRequired,
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
