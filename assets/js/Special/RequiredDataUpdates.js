'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { fetchJson } from '../component/fetchJson'
import Messages from '../component/Messages'

export default class RequiredDataUpdates extends Component {
    constructor (props) {
        super(props)
        this.functions = props.functions
        this.messages = props.messages
        this.hidden = []

        this.state = {
            loading: false,
            settings: props.settings,
            errors: []
        }
    }

    loadOptions() {
        let result = []
        result.push(<option key={'empty'}></option>)
        result.push(<option key={'required'} value={'required'}>{this.messages.required}</option>)
        result.push(<option key={'readonly'} value={'read_only'}>{this.messages.read_only}</option>)
        result.push(<option key={'hidden'} value={'hidden'}>{this.messages.hidden}</option>)
        return result
    }

    flipSettings() {
        let settings = {}
        Object.keys(this.state.settings).map(group => {
            let list = this.state.settings[group]
            Object.keys(list).map(name => {
                if (typeof settings[name] === 'undefined') {
                    settings[name] = {}
                }
                settings[name][group] = list[name]
            })
        })

        return settings
    }

    getFormRows() {
        let settings = this.flipSettings()
        let elements = []
        let hidden = []
        let loop = 0
        let count = 0
        Object.keys(settings).map(name => {
            let row = []
            let first = settings[name][Object.keys(settings[name])[0]]
            row.push(<td className={'w-1/3'} key={loop++}>{first.label}</td>)
            if (first.value === 'fixed') {
                let id = 'required_data_updates_staff_' + name
                let full_name = 'required_data_updates[staff][' + name + ']'
                row.push(<td className={'w-2/3'} key={loop++} colSpan={4}><div className={'flex-1 relative w-full'}>{this.messages.never_required}
                </div></td>)
            } else {
                Object.keys(settings[name]).map(group => {
                    let id = 'required_data_updates_' + group + '_' + name
                    let full_name = 'required_data_updates[' + group + '][' + name + ']'
                    if (settings[name][group].value === 'fixed') {
                        hidden.push(<input key={loop++} type='hidden' defaultValue={'fixed'} id={id} name={full_name} />)
                    } else {
                        row.push(<td className={'w-1/6'} key={loop++}>
                            <div className={'flex-1 relative w-full'}>
                                <select id={id} name={full_name} className={'w-full'} defaultValue={settings[name][group].value} onChange={(e) => this.changeValue(e,group,name)}>
                                    {this.loadOptions()}
                                </select>
                            </div>
                        </td>)
                    }
                })
            }
            elements.push(<tr key={loop++}>{row}</tr>)
            count++
            if (count > 9) {
                count = 0
                elements = this.renderSubmitButton(elements, loop)
                loop++
                elements.push(this.headerRow(loop))
                loop++
            }
        })
        this.hidden = hidden
        if (count > 3) {
            elements = this.renderSubmitButton(elements, loop)
        }
        return elements
    }

    renderSubmitButton(elements, loop) {
        elements.push(<tr key={loop++}><td colSpan="5"><div className="flex- relative w-full">
            <button className="btn-gibbon" id={ 'required_data_updates_submit_' + loop } name={ 'required_data_updates[submit][' + loop + ']' } type="button" style={{float: 'right'}} onClick={(e) => this.submitForm()}>
        {this.messages.submit}</button></div></td></tr>)
        return elements
    }

    changeValue(e,group,name) {
        let settings = {...this.state.settings}
        let value = e.target.value
        settings[group][name].value = value
        this.setState({
            settings: settings
        })
    }

    formData() {
        let result = {...this.state.settings}
        Object.keys(result).map(group => {
            Object.keys(result[group]).map(name => {
                if (result[group][name].value !== 'fixed') {
                    result[group][name] = result[group][name].value
                } else {
                    delete result[group][name]
                }
            })
        })
        let w = {}
        w['requiredDataUpdates'] = result
        return w
    }

    submitForm() {
        if (this.state.loading)
            return
        this.setState({
            loading: true
        })
        let data = this.formData()
        fetchJson(
            '/updater/store/required/',
            {method: 'POST', body: JSON.stringify(data)},
            false)
            .then(data => {
                this.setState({
                    loading: false,
                    settings: data.settings.settings,
                    errors: data.errors
                })
            })
    }

    headerRow(loop) {
        return (<tr className={ 'break heading' } key={loop}>
                <td className="px-2 border-b-0 sm:border-b border-t-0 w-1/3">{this.messages.Field}</td>
                <td className="px-2 border-b-0 sm:border-b border-t-0 w-1/6"> {this.messages.Staff}</td>
                <td className="px-2 border-b-0 sm:border-b border-t-0 w-1/6"> {this.messages.Student}</td>
                <td className="px-2 border-b-0 sm:border-b border-t-0 w-1/6"> {this.messages.Parent}</td>
                <td className="px-2 border-b-0 sm:border-b border-t-0 w-1/6"> {this.messages.Other}</td>
            </tr>)
    }

    render () {
        return (
            <form action={'/updater/store/required/'} method={'POST'} id={'required_data_updates'}>
                <h3>{ this.messages.required_fields_header }</h3>
                <p>{ this.messages.required_fields_help }</p>
                <Messages messages={this.state.errors} translate={this.functions.translate} />
                <table className="fullWidth rowHighlight relative">
                    <tbody>
                        {this.headerRow('initial')}
                        {this.getFormRows()}
                    </tbody>
                </table>
                {this.hidden}
            </form>
        )
    }
}

RequiredDataUpdates.propTypes = {
    functions: PropTypes.object.isRequired,
    messages: PropTypes.object.isRequired,
    settings: PropTypes.object.isRequired,
}
