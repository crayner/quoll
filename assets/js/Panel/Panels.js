'use strict'

import React from "react"
import PropTypes from 'prop-types'
import { Tab, Tabs, TabList, TabPanel } from 'react-tabs';
import '../../css/react-tabs.scss';
import Parser from "html-react-parser"
import FormApp from "../Form/FormApp"
import PaginationApp from "../Pagination/PaginationApp"
import SpecialApp from '../Special/SpecialApp'

export default function Panels(props) {
    const {
        panels,
        selectedIndex,
        functions,
        externalContent,
        singleForm,
        translations,
        panelErrors,
        hideSingleFormWarning,
    } = props

    const tabTags = Object.keys(panels).map(name => {
        let tab = panels[name]
        let showError = panelErrors[name] !== undefined ? 'text-red-400' : ''
        let title = panelErrors[name] !== undefined ? translations['Errors on Tab'] : ''
        return (
            <Tab
                key={tab.name}
                disabled={tab.disabled}>
                <span className={'tab-span ' + showError} title={title}>{tab.label}</span>
            </Tab>
        )
    })

    const content = Object.keys(panels).map(name => {
        const panel = panels[name]
        const panelContent = renderPanelContent(panel, props)
        let preContent = []
        let postContent = []
        let special = []
        if (panel.preContent !== null) {
            preContent = panel.preContent.map(name => {
                if (typeof externalContent[name] !== 'undefined')
                    return renderExternalContent(externalContent[name], functions)
                return Parser(name)
            })
        }

        if (panel.special !== null) {
            special.push(<SpecialApp {...panel.special} functions={functions} key={'special'} />)
        }

        if (panel.postContent !== null) {
            console.log(panel)
            postContent = panel.postContent.map(name => {
                if (typeof externalContent[name] !== 'undefined')
                    return renderExternalContent(externalContent[name], functions)
                return Parser(name)
            })
        }

        return (
            <TabPanel key={name}>
                {preContent}
                {special}
                {panelContent}
                {postContent}
            </TabPanel>
        )
    })

    if (singleForm) {
        let form = props.forms[Object.keys(props.forms)[0]]
        let warning = null
        if (!hideSingleFormWarning) {
            warning = <div className={'info clear-both'}>{translations['All fields on all panels are saved together.']}</div>
        }
        return (
            <form
                action={form.action}
                id={form.id}
                {...form.attr}
                method={form.method !== undefined ? form.method : 'POST'}>
                {warning}
                <Tabs selectedIndex={selectedIndex} onSelect={tabIndex => functions.onSelectTab(tabIndex)}>
                    <TabList>
                        {tabTags}
                    </TabList>
                    {content}
                </Tabs>
            </form>
        )
    }

    return (
        <Tabs selectedIndex={selectedIndex} onSelect={tabIndex => functions.onSelectTab(tabIndex)}>
            <TabList>
                {tabTags}
            </TabList>
            {content}
        </Tabs>
    )

}

Panels.propTypes = {
    panels: PropTypes.object.isRequired,
    forms: PropTypes.object.isRequired,
    externalContent: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
    panelErrors: PropTypes.object.isRequired,
    selectedIndex: PropTypes.number.isRequired,
    singleForm: PropTypes.bool.isRequired,
    hideSingleFormWarning: PropTypes.bool.isRequired,
}

function renderPanelContent(panel, props){
    if (Object.keys(panel.pagination).length > 0) {
        return (<PaginationApp {...panel.pagination} functions={props.functions} />)
    }

    if (null !== panel.content){
        return Parser(panel.content)
    }

    let form = props.forms[panel.name]
    if (typeof form === 'undefined')
        form = props.forms[Object.keys(props.forms)[0]]

    return <FormApp
        {...props}
        formName={panel.name}
        form={form} />
}

function renderExternalContent(data,functions){
    if (data.loader.type === 'pagination') {
        return (<PaginationApp key={data.loader.target} {...data.content} functions={functions} />)
    }
    if (data.loader.type === 'text') {
        return (<section key={data.loader.target}>{Parser(data.content)}</section>)
    }
    if (data.loader.type === 'html') {
        return (<section key={data.loader.target}>{renderExternalHTML(JSON.parse(data.content), 0, functions)}</section>)
    }
    console.log(data)
}

function renderExternalHTML(content, key, functions) {
    key = key + 1
    return Object.keys(content).map(elementName => {
        let children = []
        let element = content[elementName]
        if (typeof element.children === 'undefined')
            element.children = {}
        if (Object.keys(element.children).length > 0) {
            children = renderExternalHTML(element.children, key, functions)
        }
        if (elementName === 'h3') {
            key = key + 1
            return (<h3 key={key} {...element.attr}>{children}{element.content}</h3>)
        }
        if (elementName === 'span') {
            key = key + 1
            return (<span key={key} {...element.attr}>{children}{element.content}</span>)
        }
        if (elementName === 'button') {
            key = key + 1
            if (typeof element.onClick === 'object' && functions[element.onClick.function])
            {
                if (element.onClick.function === 'openUrl') {
                    element.attr.onClick = () => functions.openUrl(element.onClick.url, element.onClick.target)
                }
            }
            return (<button key={key} {...element.attr}>{children}{element.content}</button>)
        }
        console.log(elementName,element)
    })

}

