'use strict'

import React from "react"
import PropTypes from 'prop-types'
import Parser from "html-react-parser"
import Login from "./LoginApp"
import ModuleMenu from "./ModuleMenuApp"

export default function SideBarContent(props) {
    const {
        content,
        sidebarContentAttr,
        functions,
    } = props


    let sortedContent = {}
    for(let i=1; i<100; i++) {
        Object.keys(content).map(name => {
            let item = content[name]
            if (item.priority === i)
                sortedContent[name] = item
        })
    }

    let result = []
    let counter = 0
    Object.keys(sortedContent).map(name => {
        let item = content[name]
        counter++
        if (item.name === 'Login') {
            result.push(<Login login={item.login} googleOAuth={item.googleOAuth} translations={item.translations}
                               key={'Login'}/>)
        } else if (item.name === 'Module Menu') {
            result.push(<ModuleMenu data={item.data} getContent={functions.getContent} key={'Module Menu'} />)
        } else if (item.name === 'Photo') {
            if (item.exists)
                result.push(<img src={item.url} title={item.title} className={item.className} style={{width: item.width + 'px'}} key={name + counter} />)
        } else if (item.name !== 'Module Menu' && item.content !== '') {
            let x = Parser(item.content)
            if (typeof x._owner === 'object') {
                result.push(<div className={"column-no-break"} key={item.name}>{x}</div>)
                return
            }
            let y = x.filter(item => {
                if (typeof item === 'object')
                    return item
            })

            result.push(<div className={"column-no-break"} key={item.name}>{y}</div>)
        }
    })
    return (
        <div {...sidebarContentAttr}>{result}</div>
    )
}

SideBarContent.propTypes = {
    content: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.array
    ]).isRequired,
    sidebarContentAttr: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
}