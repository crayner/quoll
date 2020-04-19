'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function MinorLinks(props) {
    const {
        links
    } = props

    let content = []

    links.map((link,key) => {
        if (link.url === '') {
            content.push(<span key={key}>{link.text}</span>)
            content.push(<span key={key + '.dot'}>{' . '}</span>)
        } else {
            content.push(<a href={link.url} className={'link-white'} key={key} target={link.target}>{link.text}</a>)
            content.push(<span key={key + '.dot'}>{' . '}</span>)
        }
    })
    content.pop()

    return (
        <div id="minorLinks" className="mx-auto max-w-6xl text-right text-white text-xs md:text-sm px-2 xl:px-0 mt-2">
            {content}
        </div>
    )
}

MinorLinks.propTypes = {
    links: PropTypes.array.isRequired,
}

MinorLinks.defaultProps = {
    links: [],
}