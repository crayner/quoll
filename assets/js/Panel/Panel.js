'use strict'

import React from "react"
import PropTypes from 'prop-types'
import SectionForm from '../Section/SectionForm'
import PaginationApp from '../Pagination/PaginationApp'
import Parser from 'react-html-parser'

export default function Panel(props) {
    const {
        panel,
        singlePanel
    } = props

    let sections = []

    Object.keys(panel.sections).map(sectionKey => {
        const section = panel.sections[sectionKey]
        if (section.style === 'form') {
            sections.push(<SectionForm {...props} singlePanel={singlePanel} section={section} key={sectionKey}/>)
        } else if (section.style === 'pagination') {
            const pagination = section.content
            sections.push(<PaginationApp {...props} {...pagination} key={sectionKey} />)
        } else if (section.style === 'html') {
            sections.push(Parser(section.content))
        } else {
            console.log(props,section)
            console.error('Section style [' + section.style + '] is not defined.')
        }
    })

    return (sections)
}

Panel.propTypes = {
    panel: PropTypes.object.isRequired,
    singlePanel: PropTypes.bool,
}

Panel.defaultProps = {
    singlePanel: false,
}