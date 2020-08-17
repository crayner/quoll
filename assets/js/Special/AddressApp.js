'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Messages from '../component/Messages'
import Widget from '../Form/Widget'
import Row from '../Form/Template/Table/Row'
import { fetchJson } from '../component/fetchJson'

export default class AddressApp extends Component {
    constructor (props) {
        super(props)
        this.functions = props.functions
        this.functions['changeAddress'] = this.changeAddress.bind(this)
        this.functions['changeLocality'] = this.changeLocality.bind(this)
        this.functions['submitForm'] = this.submitForm.bind(this)

        this.state = {
            loading: false,
            address: props.address_form,
            locality: props.locality_form,
            localityList: props.locality_list,
            localityChoices: props.locality_choices,
            showLocality: false,
            errors: [],
            address_id: props.address_id,
            locality_id: props.locality_id,
        }
    }

    changeAddress(e, form) {
        let value = e.target.value
        let name = form.name
        let address = {...this.state.address}
        address.children[name].value = value
        let locId = this.state.locality_id
        if (name === 'locality') {
            locId = value
        }

        if (name === 'streetName' && address.children.streetNumber.value === '') {
            let x = value.split(' ')
            if (x.length > 1) {
                if (x[0].match(/^\d+$/) !== null) {
                    address.children.streetNumber.value = x[0]
                    delete x[0]
                    address.children[name].value = x.join(' ').trim()
                } else if (x[1].match(/^\d+$/) !== null && address.children.streetNumber.value === '') {
                    address.children.streetNumber.value = (x[0] + ' ' + x[1]).trim()
                    delete x[0]
                    delete x[1]
                    address.children[name].value = x.join(' ').trim()
                }
            }
        }


        this.setState({
            address: address,
            locality_id: locId,
        })
    }

    changeLocality(e, form) {
        let value = e.target.value
        let name = form.name
        let locality = {...this.state.locality}
        locality.children[name].value = value
        this.setState({
            locality: locality
        })
    }

    getLocalityChoices() {
        let result = []
        result.push(<option key={'placeholder'} />)
        this.state.localityChoices.map((choice, key) => {
            result.push(<option key={key} value={choice.value}>{choice.label}</option>)
        })
        return result
    }

    addLocality() {
        let form = {...this.state.locality}
        if (this.state.locality_id > 0)
        {
            let locality = this.state.localityList[this.state.locality_id]
            form.children.name.value = locality.name
            form.children.territory.value = locality.territory
            form.children.country.value = locality.country
            form.children.postCode.value = locality.postCode
        }

        this.setState({
            showLocality: true,
            locality: form,
            errors: [],
        })
    }

    clearLocality() {
        let form = {...this.state.locality}
        form.children.name.value = ''
        form.children.territory.value = ''
        form.children.country.value = ''
        form.children.postCode.value = ''

        this.setState({
            showLocality: true,
            locality_id: 0,
            locality: form,
            errors: []
        })
    }

    submitForm() {
        if (this.state.loading)
            return
        this.setState({
            loading: !this.state.loading,
            errors: [{class: 'info', message: 'Let me ponder your request'}],
        })
        if (this.state.showLocality)
            return this.submitLocality()
        let data = {
            address: {}
        }
        let address = {...this.state.address}
        data.address['flatUnitDetails'] = address.children.flatUnitDetails.value
        data.address['streetNumber'] = address.children.streetNumber.value
        data.address['streetName'] = address.children.streetName.value
        data.address['propertyName'] = address.children.propertyName.value
        data.address['locality'] = address.children.locality.value
        data.address['_token'] = address.children._token.value
        data.address['id'] = this.state.address_id

        let url = '/address/' + this.state.address_id + '/edit/popup/'
        if (this.state.address_id === 0) {
            url = '/address/add/popup/'
        }
        fetchJson(
            url,
            {method: 'POST', body: JSON.stringify(data)},
            false
            ).then(data => {
                this.setState({
                    loading: false,
                    address: data.form,
                    errors: data.errors,
                    locality_id: data.locality_id,
                    address_id: data.address_id,
                })
            })
    }
    
    submitLocality() {
        let data = {
            locality: {}
        }
        let locality = {...this.state.locality}
        data.locality['name'] = locality.children.name.value
        data.locality['territory'] = locality.children.territory.value
        data.locality['country'] = locality.children.country.value
        data.locality['postCode'] = locality.children.postCode.value
        data.locality['_token'] = locality.children._token.value
        let url = '/locality/' + this.state.locality_id + '/edit/'

        if (this.state.locality_id === 0) {
            url = '/locality/add/'
        }

        fetchJson(
            url,
            {method: 'POST', body: JSON.stringify(data)},
            false
        ).then(data => {
            if (data.status === 'success') {
                let address = {...this.state.address}
                address.children.locality.value = data.locality_id
                this.setState({
                    loading: false,
                    locality: data.form,
                    address: address,
                    errors: data.errors,
                    showLocality: false,
                    localityList: data.locality_list,
                    localityChoices: data.locality_choices,
                    locality_id: data.locality_id
                })
            } else {
                this.setState({
                    loading: false,
                    locality: data.form,
                    errors: data.errors,
                })

            }
        })
    }

    backToAddress() {
        this.setState({
            showLocality: false,
        })
    }
    
    clearAddress() {
        let form = {...this.state.address}
        form.children.flatUnitDetails.value = ''
        form.children.streetNumber.value = ''
        form.children.streetName.value = ''
        form.children.propertyName.value = ''
        form.children.locality.value = 0

        let locality = {...this.state.locality}
        locality.children.name.value = ''
        locality.children.territory.value = ''
        locality.children.country.value = ''
        locality.children.postCode.value = ''


        this.setState({
            showLocality: false,
            locality_id: 0,
            address_id: 0,
            locality: locality,
            address: form,
            errors: []
        })

    }

    render () {
        if (this.state.showLocality) {
            return (
                <form className={'smallIntBorder fullWidth standardForm'}>
                    <Messages messages={this.state.errors} translate={this.functions.translate} />
                    <Widget form={this.state.locality.children._token} functions={this.functions} columns={2} />
                    <table className={'smallIntBorder fullWidth standardForm relative'}>
                        <tbody>
                            <Row form={this.state.locality.children.localityHeader} functions={this.functions} columns={2} />
                            <Row form={this.state.locality.children.name} functions={this.functions} columns={2} />
                            <Row form={this.state.locality.children.territory} functions={this.functions} columns={2} />
                            <Row form={this.state.locality.children.postCode} functions={this.functions} columns={2} />
                            <Row form={this.state.locality.children.country} functions={this.functions} columns={2} />
                            <Row form={this.state.locality.children.submit} functions={this.functions} columns={2} />
                        </tbody>
                    </table>
                </form>)
        }
        let locality = this.state.address.children.locality
        if (typeof locality.errors === 'undefined') {
            locality.errors = []
        } else {
            locality.errors = locality.errors.map((error,key) => {
                return (<li key={key}>{error}</li>)
            })
        }
        return (
            <form className={'smallIntBorder fullWidth standardForm'}>
                <Messages messages={this.state.errors} translate={this.functions.translate} />
                <table className={'smallIntBorder fullWidth standardForm relative'}>
                    <tbody>
                        <Row form={this.state.address.children.addressHeader} functions={this.functions} columns={2} />
                        <Row form={this.state.address.children.flatUnitDetails} functions={this.functions} columns={2} />
                        <Row form={this.state.address.children.streetNumber} functions={this.functions} columns={2} />
                        <Row form={this.state.address.children.streetName} functions={this.functions} columns={2} />
                        <Row form={this.state.address.children.propertyName} functions={this.functions} columns={2} />
                        <tr className="flex flex-col sm:flex-row justify-between content-center p-0">
                            <td className="flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0">
                                <label htmlFor="address_locality" className="inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">{locality.label}<br/>
                                <span className="text-xs text-gray-600 italic font-normal mt-1 sm:mt-0" id={'address_locality_help'}>{locality.help}</span></label>
                            </td>
                            <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0">
                                <div className={'flex-1 relative float-left' + (locality.errors.length > 0 ? ' errors' : '')}>
                                    <select className={'w-full mr-8 less32'} id={locality.id} name={locality.full_name} value={locality.value} onChange={(e) => this.functions.changeAddress(e,locality)}>
                                        {this.getLocalityChoices()}
                                    </select>
                                    <div className={'button-right'}>
                                        <button type={'button'} title={this.functions.translate('File Download')} className={'button float-right ml-6'} onClick={() => this.addLocality()}><span className={'fa-fw fas fa-plus-circle text-gray-800 hover:text-green-500'}></span></button>
                                    </div>
                                    {locality.errors.length > 0 ? <ul>{locality.errors}</ul> : ''}
                                </div>
                            </td>
                        </tr>
                        <Row form={this.state.address.children.submit} functions={this.functions} columns={2} />
                    </tbody>
                </table>
                <Widget form={this.state.address.children._token} functions={this.functions} columns={2}/>
                <Widget form={this.state.address.children.id} functions={this.functions} columns={2} />
            </form>)
    }
}

AddressApp.propTypes = {
    functions: PropTypes.object.isRequired,
    address_form: PropTypes.object.isRequired,
    locality_form: PropTypes.object.isRequired,
    locality_list: PropTypes.object,
    locality_choices: PropTypes.array.isRequired,
    address_id: PropTypes.number.isRequired,
    locality_id: PropTypes.number.isRequired,
}
AddressApp.defaultProps = {
    locality_list: {},
}
