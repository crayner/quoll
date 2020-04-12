'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'

export default class HeaderMenu extends Component {
    constructor(props) {
        super(props)
        this.menu = props.menu
        this.translations = props.translations

        this.onMouseLeave = this.onMouseLeave.bind(this)
        this.onMouseHover = this.onMouseHover.bind(this)

        this.state = {
            showMenu: [],
            toggleMenu: false
        }
    }

    generateMenu() {
        let result = []

        result.push(
            <div className="flex flex-wrap items-center m-0 px-2 border-t border-b" key={'Home'}>
                <div className="pl-2 mt-1">
                    <div><a className={'block uppercase font-bold text-sm text-gray-800 hover:text-purple-600 no-underline px-2 py-3'} href={'/home/'} title={this.translate('Home')}>{this.translate('Home')}</a> </div>
                </div>
            </div>
        )

        if (this.menu === false)
            return result

        Object.keys(this.menu).map(key => {
            const menu = this.menu[key]
            result.push(
                <div className="flex flex-wrap items-center m-0 px-2 border-t border-b" key={key} onMouseEnter={() => this.onMouseHover(key)} onMouseLeave={() => this.onMouseLeave()}>
                    <div className="pl-2 mt-1">
                        <div><a className={'block uppercase font-bold text-sm text-gray-800 hover:text-purple-600 no-underline px-2 py-3'}title={key}>{key}</a> </div>
                    </div>
                    {this.generateMenuItems(key, menu)}
                </div>
            )
        })


        return result
    }

    generateMenuItems(name, menu){
        if(menu.length === 0)
            return ''
        let result = []

        menu.map((item,key) => {
            result.push(
                <li className="hover:bg-purple-700" key={key}><a className={'block text-sm text-white focus:text-purple-200 text-left no-underline px-1 py-2 md:py-1 leading-normal'} href={item.href} title={item.name}>{item.name}</a></li>
            )
        })

        let listClass = 'list-none flex flex-wrap items-center m-0 px-2 border-t border-b'
        if (!this.state.showMenu.includes(name))
            listClass = listClass + ' hidden'

        return (
            <ul className={listClass}>
                {result}
            </ul>
        )
    }

    onMouseHover(name) {
        let showMenu = [name]
        this.setState({
            showMenu: showMenu
        })
    }

    onMouseLeave() {
        this.setState({
            showMenu: []
        })
    }

    translate(id) {
        if (typeof this.translations[id] === 'undefined') {
            console.error('Translation failed for ' + id)
            return id
        }
        return this.translations[id]
    }

    toggleMenu()
    {
        let toggle = document.getElementById('top-menu')
        if(this.state.toggleMenu) {
            toggle.setAttribute('data-status', 'hidden')
        } else {
            toggle.setAttribute('data-status', 'show')
        }
        this.setState({
            toggleMenu: !this.state.toggleMenu
        })
    }

    render () {
        return (<div>
            <a className={'float-left md:hidden flex pt-2'} id={'hamburger'} onClick={() => this.toggleMenu()}><span className={'fas fa-bars fa-fw fa-2x text-gray-500'}/></a>
            <div id={'top-menu'} data-status={'hidden'}>
                {this.generateMenu()}
            </div>
            <div className="notificationTray self-end flex-grow" id="notificationTray" style={{float: 'right'}}>
            </div>
        </div>)
    }
}

HeaderMenu.propTypes = {
    menu: PropTypes.oneOfType([
        PropTypes.object,
        PropTypes.bool,
    ]).isRequired,
    translations: PropTypes.object.isRequired,
}
