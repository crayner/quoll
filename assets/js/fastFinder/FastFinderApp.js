'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Autosuggest from 'react-autosuggest'
import {openPage} from "../component/openPage"

export default class FastFinderApp extends Component {
    constructor (props) {
        super(props)
        this.fastFindChoices = props.fastFindChoices
        this.state = {
            value: '',
            suggestions: [],
            fastFinderClass: 'md:block absolute md:static top-0 right-0 w-full md:max-w-md p-2 sm:p-4 hidden',
        }

        this.onSuggestionsFetchRequested = this.onSuggestionsFetchRequested.bind(this)
        this.onSuggestionsClearRequested = this.onSuggestionsClearRequested.bind(this)
        this.onChange = this.onChange.bind(this)
        this.getSuggestionValue = this.getSuggestionValue.bind(this);
        this.renderSuggestion = this.renderSuggestion.bind(this);
        this.toggleFastFinderClass = this.toggleFastFinderClass.bind(this);
    }

    // Autosuggest will call this function every time you need to update suggestions.
    // You already implemented this logic above, so just use it.
    onSuggestionsFetchRequested(value) {
        if (value.value.length > 0) {
            this.setState({
                suggestions: this.getSuggestions(value.value)
            })
        }
    }

    // Autosuggest will call this function every time you need to clear suggestions.
    onSuggestionsClearRequested() {
        this.setState({
            suggestions: []
        })
    }

    onChange(event) {
        if (event.target.value !== undefined)
        {
            this.setState({
                value: event.target.value
            })
        }
    }

    getSuggestions(value) {
        var suggestions = []
        value = value.trim().toLowerCase()
        if (value.length > 1) {
            this.fastFindChoices.filter(group => {
                var x = group.suggestions.filter(row => {
                    var search = row.text + ' ' + row.search
                    search = search.trim().toLowerCase()
                    return search.includes(value)
                })
                if (x.length > 0) {
                    var z = x.map(sugg => {
                        return {id: sugg.id, text: group.prefix + ' - ' + sugg.text}
                    })
                    suggestions = suggestions.concat(z)
                }
            })
        }
        return suggestions
    }

    getSuggestionValue(suggestion) {
        var url = "/finder/{id}/redirect/"
        const id = btoa(suggestion.id)
        url = url.replace('{id}', id)
        openPage(url, [], false)
        this.setState({
            value: suggestion.text
        })
    }

    renderSuggestion(suggestion) {
        return (<span>{suggestion.text}</span>)
    }

    toggleFastFinderClass()
    {
        var fastFinderClass = 'md:block absolute md:static top-0 right-0 w-full md:max-w-md p-2 sm:p-4'
        if (this.state.fastFinderClass === fastFinderClass) {
            fastFinderClass = 'md:block absolute md:static top-0 right-0 w-full md:max-w-md p-2 sm:p-4 hidden'
        }
        this.setState({
            fastFinderClass: fastFinderClass
        })
    }

    render () {
        return (
            <div className={'flex-grow flex justify-end'}>
                <button data-toggle="#fastFinder"
                        className="flex md:hidden items-center rounded bg-gray-300 mr-4 px-4 py-3 text-base active" onClick={this.toggleFastFinderClass}>
                    <span className="hidden sm:inline text-gray-600 text-xs font-bold uppercase pr-2">{ this.props.trans_fastFind } </span>
                    <span className={'fas fa-search fa-fw fa-2x text-gray-600'} title={ this.props.trans_fastFind }></span>
                </button>
                <div id="fastFinder" className={ this.state.fastFinderClass } style={{maxWidth: '350px'}}>
                    <div className="z-10 rounded border border-solid border-gray-300" style={{backgroundColor: '#fbfbfb'}}>
                        <a data-toggle="#fastFinder" className="p-2 pl-4 float-right text-xs underline md:hidden text-gray-600 "
                           href="#" onClick={this.toggleFastFinderClass}><span className={'far fa-times-circle fa-fw'} title={ this.props.trans_close }></span></a>

                        <div className="py-2 md:py-1 px-2 border-solid border-0 border-b border-gray-300 md:text-right text-gray-800 text-xxs font-bold uppercase">
                            { this.props.trans_fastFind }: { this.props.trans_fastFindActions }
                        </div>

                        <div className="w-full px-2 sm:py-2">
                            <div className="flex-1 relative">
                                <Autosuggest
                                    id={'token-input-fastFinderSearch'}
                                    suggestions={this.state.suggestions}
                                    onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
                                    onSuggestionsClearRequested={this.onSuggestionsClearRequested}
                                    getSuggestionValue={this.getSuggestionValue}
                                    renderSuggestion={this.renderSuggestion}
                                    inputProps={{
                                        value: this.state.value,
                                        placeholder: this.props.trans_placeholder,
                                        onChange: this.onChange,
                                        className: 'w-full',
                                    }}
                                />
                            </div>
                        </div>

                        {this.props.roleCategory === 'Staff' ?
                            <div className="py-1 px-2 text-right text-gray-500 text-xxs font-normal italic">
                                { this.props.trans_enrolmentCount }
                            </div>
                            : ''}
                    </div>
                </div>
            </div>
        )
    }
}

FastFinderApp.propTypes = {
    fastFindChoices: PropTypes.array,
}
