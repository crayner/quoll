'use strict'

import React from "react"
import PropTypes from 'prop-types'
import SectionForm from '../Section/SectionForm'
import PaginationApp from '../Pagination/PaginationApp'
import Parser from 'react-html-parser'
import SpecialApp from '../Special/SpecialApp'

export default function Panel(props) {
    const {
        panelName,
        singlePanel,
        panels,
    } = props

    let sections = []
    const panel = {...panels[panelName]}

    Object.keys(panel.sections).map(sectionKey => {
        const section = panel.sections[sectionKey]
        if (section.style === 'form') {
            sections.push(<section className={'panel_section'} key={sectionKey}><SectionForm {...props} singlePanel={singlePanel} section={section}/></section>)
        } else if (section.style === 'pagination') {
            const pagination = section.content
            sections.push(<section className={'panel_section'} key={sectionKey}><PaginationApp {...props} {...pagination} /></section>)
        } else if (section.style === 'html') {
            sections.push(<section className={'panel_section'} key={sectionKey}>{Parser(section.content)}</section>)
        } else if (section.style === 'special') {
            const special = section.content
            sections.push(<section className={'panel_section'} key={sectionKey}><SpecialApp {...special} {...props} name={section.content.name} /></section>)
        } else {
            console.log(props,section)
            const error = 'Section style [' + section.style + '] is not defined.'
            console.error(error)
            sections.push(<section className={'panel_section'} key={sectionKey}>{error}</section>)
        }
    })

    return (sections)
}

Panel.propTypes = {
    panels: PropTypes.object.isRequired,
    singlePanel: PropTypes.bool,
    panelName: PropTypes.string.isRequired,
}

Panel.defaultProps = {
    singlePanel: false,
}