'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function Header(props) {
    const {
        details,
    } = props

    let menu = []
    if (Object.keys(details.menu).length > 0) {
        Object.keys(details.menu).map(categoryName => {
            const items = details.menu[categoryName]

            const itemContent = Object.keys(items).map(key => {
                const item = items[key]
                return (<li className="hover:bg-purple-700" key={key}>
                    <a className="block text-sm text-white focus:text-purple-200 text-left no-underline px-1 py-2 md:py-1 leading-normal"
                       href={item.url}>{item.name}</a>
                </li>)
            })

            menu.push(<li className="sm:relative group mt-1" key={categoryName}>
                    <a className="block uppercase font-bold text-sm text-gray-800 hover:text-indigo-500 no-underline px-2 py-3"
                       href="#">{categoryName}</a>
                    <ul className="list-none bg-transparent-900 absolute hidden group-hover:block w-full sm:w-48 left-0 m-0 -mt-1 py-1 sm:p-1 z-50">
                        {itemContent}
                    </ul>
                </li>
            )
        })
    }

    return (
        <div id="headerWrapper">
            <div id="header" className="relative bg-white flex justify-between items-center rounded-t h-24 sm:h-32">
                <a id="header-logo" href={details.homeURL}>
                    <img title={details.organisationName !== false ? details.organisationName : 'Kookaburra'}
                         src={details.organisationLogo}
                         style={{width: '400px'}} />
                </a>
                <div id="fastFinderWrapper" className={'flex-grow flex justify-end'}></div>
            </div>

            <nav id="header-menu" className="w-full bg-gray-200 justify-between">
                <ul className="list-none flex flex-wrap items-center m-0 px-2 border-t border-b">
                    <li className="pl-2 mt-1" key={'home'}>
                        <a className="block uppercase font-bold text-sm text-gray-800 hover:text-indigo-500 no-underline px-2 py-3"
                           href={details.homeURL}>{details.translations.Home}</a>
                    </li>
                    {menu}
                    <li className="notificationTray self-end flex-grow" id="notificationTray" key={'notificationTray'}>

                    </li>
                </ul>
            </nav>
        </div>
    )
}

Header.propTypes = {
    details: PropTypes.object.isRequired,
}
