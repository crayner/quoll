'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import {fetchJson} from "../component/fetchJson"

export default class PermissionApp extends Component {
    constructor (props) {
        super(props)
        this.roles = props.roles
        this.translations = props.translations
        this.columnCount = props.roles.length + 1
        this.modules = props.modules

        this.state = {
            module: '',
            search: '',
            content: props.content
        }
    }

    getHeader()
    {
        let columns = []
        columns.push(<th className={'text-xxs sm:text-xs p-2 sm:py-3 w-1/2'} key={'action'}>{this.translations['Action']}</th>)
        this.roles.map((value,id) => {
            columns.push(<th key={id} className={'text-xxs sm:text-xs p-2 sm:py-3'}><div className={'tooltip'}>{value['nameShort']}<span className={'tooltiptext'}>{value['name']}</span></div></th>)
        })

        return columns
    }

    getFilter()
    {
        let result = []

        return (<tr><td colSpan={this.columnCount} className={'bg-white'}>
            <table className="noIntBorder fullWidth relative">
                <tbody>


                    <tr className="flex flex-col sm:flex-row justify-between content-center p-0">
                        <td className="flex flex-col flex-grow justify-center -mb-1 sm:mb-0 px-2 border-b-0 sm:border-b border-t-0">
                            <label htmlFor="search_input">{this.translations['Search for']}</label>
                            <span id="manage_search_search_help"
                                  className="text-xxs text-gray-600 italic font-normal mt-1 sm:mt-0 help-text" />
                        </td>
                        <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0">
                            <div className="flex-1 relative">
                                <input type="text" id="search_input" className="w-full" value={this.state.search} onChange={(e) => this.changeSearch(e)} />
                                <div className="button-right">
                                    <button type="button" title="Clear" className="button" onClick={() => this.clearSearchFilter()}>
                                        <span className="fa-fw fas fa-broom" />
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>



                    <tr className="flex flex-col sm:flex-row justify-between content-center p-0">
                        <td className="flex flex-col flex-grow justify-center -mb-1 sm:mb-0 px-2 border-b-0 sm:border-b border-t-0">
                            <label htmlFor="manage_search_search">{this.translations['Filter Select']}</label>
                            <div style={{marginTop: '7px', height: '20px'}}>
                            </div>
                        </td>
                        <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0">
                            <div className="flex-1 relative">
                                <select id="filter_select" className="w-full" value={this.state.module} onChange={(e) => this.changeFilter(e)}>
                                    <option value="">{this.translations['Filter']}</option>
                                    <optgroup label={this.translations['Module']}>
                                        {this.getFilterOptions()}
                                    </optgroup>
                                </select>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td></tr>)
    }

    getFilterOptions()
    {
        return this.modules.map(value => {
            return (<option key={value.name} value={value.name}>{value.name}</option>)
        })
    }


    changeSearch(e)
    {
        let value = e.target.value
        this.setState({
            search: value
        })
    }

    clearSearchFilter() {
        this.setState({
            module: '',
            search: ''
        })
    }

    changeFilter(e)
    {
        let value = e.target.value
        this.setState({
            module: value
        })
    }

    getContent()
    {
        let rows = []
        let module = ''
        let loop = 1
        Object.keys(this.state.content).map(id => {

            let content = this.state.content[id]
            let row = []
            let hasContent = 0

            if (this.state.module === '' || this.state.module === content.moduleName) {

                if (this.state.search === '' || content.actionName.toLowerCase().includes(this.state.search.toLowerCase())) {

                    if (module !== content.moduleName) {
                        rows.push(<tr key={loop++} className={'border-0'}>
                            <th className={'w-full pb-0 pt-8 bg-white border-0'} colSpan={this.columnCount}><h3
                                className={'m-2'}>{content.moduleName}</h3></th>
                        </tr>)
                        rows.push(<tr className={'break'} key={loop++}>{this.getHeader()}</tr>)
                        module = content.moduleName
                    }

                    row.push(<td key={'action'}>{content.actionName}</td>)
                    Object.keys(this.roles).map(x => {
                        let w = this.roles[x]
                        let role = content.roles[w['id']]
                        let roleId = role['id']

                        let attr = []
                        let span_attr = []
                        span_attr['className'] = 'fa-fw far fa-thumbs-down'
                        attr['className'] = 'button text-white bg-gray-200 hover:bg-gray-400'

                        if (role.readOnly) {
                            attr['disabled'] = true
                        }

                        if (role.checked) {
                            attr['checked'] = true
                            span_attr['className'] = 'fa-fw far fa-thumbs-up'
                            attr['className'] = 'button text-white bg-green-200 hover:bg-green-400'
                        }

                        row.push(<td key={roleId}>
                            <button type={'button'} title={this.translations['Yes/No']} {...attr} onClick={() => this.togglePermission(content.id, roleId)}>
                                <span {...span_attr} />
                            </button>
                        </td>)
                    })
                    rows.push(<tr key={loop++}>{row}</tr>)
                }
            }
        })


        if (rows.length === 0) {
            rows.push(<tr><td className={'py-24 font-bold text-xl'} style={{textAlign: 'center'}}>{this.translations['No results matched your search.']}</td></tr>)
        }

        return(<tbody>{this.getFilter()}{rows}</tbody>)
    }

    togglePermission(action,role)
    {
        let url = '/user/admin/permission/' + action + '/' + role +'/toggle/'

        fetchJson(
            url,
            [],
            false
        ).then (data => {
            this.setState({
                content: data.content
            })
            if (data.status !== 'success') {
                alert(data.errors[0]['message'])
            }
        })
    }

    render () {
        return (
            <section>
                <h3>{this.translations['Manage Permissions']}</h3>
                <p>{this.translations['permission_help']}</p>
                <table className={'w-full relative striped'}>
                    {this.getContent()}
                </table>
            </section>
        )
    }
}

PermissionApp.propTypes = {
    content: PropTypes.array.isRequired,
    roles: PropTypes.array.isRequired,
    translations: PropTypes.object.isRequired,
}
