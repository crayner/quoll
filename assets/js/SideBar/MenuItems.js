'use strict'

import React from "react"
import PropTypes from 'prop-types'
import ModuleMenu from "./ModuleMenuApp"

export default function MenuItems(props) {
    const {
        items,
        getContent
    } = props

    const itemsReturn = items.map((item, key) => {
        return (<li className="p-0 leading-normal lg:leading-tight" key={key}>
            <a onClick={() => getContent(item.url)} className={item.active ? 'active pointer-hover' : 'pointer-hover' }>{ item.name }</a>
        </li>)
    })
    return (<ul className="list-none m-0 mb-6">
            {itemsReturn}
        </ul>)
}

MenuItems.propTypes = {
    items: PropTypes.array.isRequired,
    getContent: PropTypes.func.isRequired,
}