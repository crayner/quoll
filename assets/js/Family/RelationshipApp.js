'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import {
    buildFormData,
} from '../Container/ContainerFunctions'
import { fetchJson } from '../component/fetchJson'
import Messages from '../component/Messages'

export default class RelationshipApp extends Component {
    constructor (props) {
        super(props)
        this.form = props.form
        this.functions = props.functions
        this.messages = props.messages
        this.relationships = props.relationships
        this.state = {
            loading: false
        }
    }

    getRelationshipChoices(list) {
        return list.map((choice,key) => {
            return (<option value={choice.value} key={key}>{choice.label}</option> )
        })
    }

    getRelationships() {
        let relationships = []

        if (this.state.loading) {
            relationships.push(<tr key={'key'} className={'flex flex-col sm:flex-row justify-between content-center p-0'}>
                    <td className={'px-2 border-b-0 sm:border-b border-t-0 w-full'}>
                        <div className="h-18 rounded-sm border bg-gray-100 shadow-inner overflow-hidden w-full">
                            <div className="info">
                                {this.messages.loadingContent}
                            </div>
                        </div>
                    </td>
                </tr>
            )
            return relationships
        }

        Object.keys(this.form.children.relationships.children).map(key => {
            let item = this.form.children.relationships.children[key]
            let relationship = this.relationships[key]
            console.log(item,relationship)
            let careGiver = item.children.careGiver
            let student = item.children.student
            let family = item.children.family
            let relForm = item.children.relationship
            let error = ''
            let message = []
            if (relForm.errors.length > 0) {
                error = ' errors'
                let loop = 0
                message = relForm.errors.map(mess => {
                    return (<li key={loop++}>{mess}</li>)
                })
            }
            relationships.push(
                <tr key={key} className={'flex flex-col sm:flex-row justify-between content-center p-0'}>
                    <td className="px-2 border-b-0 sm:border-b border-t-0 w-2/5">
                        <div className={'text-right pt-2'}>{relationship.care_giver}</div>
                        <input type={'hidden'} id={careGiver.id} name={careGiver.full_name} value={careGiver.value} />
                    </td>
                    <td className={'px-2 border-b-0 sm:border-b border-t-0 w-1/5'}>
                        <div className={'relative w-full' + error}>
                            <select id={relForm.id} name={relForm.full_name} defaultValue={relForm.value} className={'w-full'} onChange={(e) => this.onRelationshipChange(e,relForm)}>
                                <option />
                                {this.getRelationshipChoices(relForm.choices)}
                            </select>
                            {message}
                            <input type={'hidden'} id={family.id} name={family.full_name} value={family.value} />
                        </div>
                    </td>
                    <td className="px-2 border-b-0 sm:border-b border-t-0 w-2/5" style={{float: 'left'}}>
                        <div className={'text-left pt-2'}>{relationship.student}</div>
                        <input type={'hidden'} id={student.id} name={student.full_name} value={student.value} />
                    </td>
                </tr>)

        })
        return relationships
    }

    onRelationshipChange(e, form) {
        form.value = e.target.value

        form = this.mergeForm(this.form, form)

        this.functions.replaceSpecialContent('Relationships', {'form': form})
    }

    mergeForm(parent, child) {
        if (typeof parent.children === 'object' && Object.keys(parent.children).length > 0) {
            Object.keys(parent.children).map(key => {
                let form = parent.children[key]
                form = this.mergeForm(form,child)
                if (form.id === child.id)
                    Object.assign(parent.children[key], {...child})
            })
        }
        return {...parent}
    }

    submitForm(e,form) {
        this.setState({
            loading: true
        })
        let data = buildFormData({}, form)
        fetchJson(
            this.form.action,
            {method: this.form.method, body: JSON.stringify(data)},
            false)
            .then(data => {
                let errors = this.form.errors
                errors = errors.concat(data.errors)
                data.special.form.errors = errors
                this.setState({
                    loading: false
                })
                this.functions.replaceSpecialContent('Relationships', {'form': data.special.form});
            })
    }


    render () {
        const submit = {...this.form.children.submit}
        const token = {...this.form.children._token}
        return (<form action={this.form.action} method={'POST'} id={this.form.id}>
                    <Messages messages={this.form.errors} translate={this.functions.translate} />
                    <table className={'smallIntBorder fullWidth standardForm relative'}>
                        <tbody>
                            <tr className={'break flex flex-col sm:flex-row justify-between content-center p-0'}>
                                <td colSpan={'3'} className={'flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0'}>
                                    <h3>{ this.messages.Relationships }</h3>
                                </td>
                            </tr>
                            {this.getRelationships()}
                            <tr className="flex flex-col sm:flex-row justify-between content-center p-0">
                                <td colSpan="3" className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0">
                                    <div className="flex-1 relative">
                                        <span className="emphasis small" id={submit.id + '_help'}>{submit.help}</span>
                                        <button className="btn-gibbon" id={submit.id} onClick={(e) => this.submitForm(e,this.form)} name={submit.full_name} aria-describedby={submit.id + '_help'} type="button" style={{float: 'right'}}>{submit.label}</button>
                                        <input type={'hidden'} id={token.id} name={token.full_name} value={token.value} />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>)
    }
}

RelationshipApp.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    messages: PropTypes.object.isRequired,
    relationships: PropTypes.array.isRequired,
}
