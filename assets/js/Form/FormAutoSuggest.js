'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Autosuggest from 'react-autosuggest'

export default class FormAutoSuggest extends Component {
    constructor (props) {
        super(props)
        this.wrapper_attr = props.wrapper_attr
        this.widget_attr = props.widget_attr
        this.errors = props.errors
        this.functions = props.functions

        this.autoSuggestMatches = this.autoSuggestMatches.bind(this)
        this.resetSuggestMatches = this.resetSuggestMatches.bind(this)
        this.getSuggestedValue = this.getSuggestedValue.bind(this)
        this.renderSuggestion = this.renderSuggestion.bind(this)
        this.onChange = this.onChange.bind(this)
        this.toggleAutoSuggestList = this.toggleAutoSuggestList.bind(this)
        this.functions.clearSuggestions = this.resetSuggestMatches.bind(this)

        this.state = {
            form: props.form,
            suggestions: [],
            value: '',
            suggestionsClass: 'md:block absolute md:static top-0 right-0 w-full hidden',
        }
    }

    componentDidMount () {
        if (this.state.form.value > 0) {
            this.state.form.choices.map(choice => {
                if (this.state.form.value === choice.value) {
                    this.setState({
                        value: choice.label
                    })
                }
            })
        }
    }

    componentDidUpdate (prevProps, prevState, snapshot) {
        if (Object.values(this.props.form.choices).length !== this.state.form.choices.length || JSON.stringify(Object.values(this.props.form.choices)) !== JSON.stringify(this.state.form.choices)) {
            let form = { ...this.props.form }
            form.choices = Object.values(this.props.form.choices)
            this.setState({
                form: form,
            })
        }
    }

    onChange(event) {
        if (event.target.value !== undefined)
        {
            this.setState({
                value: event.target.value
            })
        }
    }

    getSuggestedValue(suggestion) {
        let event = {
            target: {
                value: suggestion.value
            }
        }
        let form = {...this.state.form}
        form.value = suggestion.value
        this.setState({
            value: suggestion.label,
            form: form,
        })
        this.functions.onElementChange(event, form)
    }

    renderSuggestion(suggestion) {
        return (<span>{suggestion.label}</span>)
    }

    autoSuggestMatches(value) {
        value = value.value.trim().toLowerCase()
        if (value === '') {
            return this.resetSuggestMatches()
        }
        const suggestions = this.state.form.choices.filter(choice => {
            const label = choice.label.toLowerCase()
            if (label.includes(value)) {
                return choice
            }
        })
        this.setState({
            suggestions: suggestions
        })
    }

    resetSuggestMatches() {
        this.setState({
            suggestions: [],
        })
    }

    clearAutoSuggest() {
        const event = {
            target: {
                value: ''
            }
        }
        let form = {...this.state.form}
        form.value = ''
        this.setState({
            value: '',
            form: form,
            suggestions: []
        })
        this.functions.onElementChange(event, form)
    }

    toggleAutoSuggestList()
    {
        let suggestionsClass = 'md:block absolute md:static top-0 right-0 w-full'
        if (this.state.suggestionsClass === suggestionsClass) {
            suggestionsClass = 'md:block absolute md:static top-0 right-0 w-full hidden'
        }
        this.setState({
            suggestionsClass: suggestionsClass,
        })
    }

    getButtons() {
        let buttons = []
        Object.keys(this.state.form.buttons).map(name => {
            const button = this.state.form.buttons[name]
            if (name === 'add' && this.state.form.value === '') {
                let url = {
                    url: button.route,
                    target: button.target,
                    options: button.specs,
                }
                buttons.push(<button type={'button'} key={name} title={button.title} className={'button bg-gray-100 hover:text-indigo-500'} onClick={() => this.functions.openUrl(url)}>
                    <span className={button.class}></span>
                </button>)
            }
            if (name === 'refresh' && this.state.form.value === '') {
                this.state.form.auto_refresh_url = button.route
                buttons.push(<button type={'button'} key={name} title={button.title} className={'button bg-gray-100 hover:text-indigo-500'} onClick={() => this.functions.refreshChoiceList({...this.state.form})}>
                    <span className={button.class}></span>
                </button>)
            }
            if (name === 'edit' && this.state.form.value !== '') {
                let route = button.route.replace("__value__", this.state.form.value)
                let url = {
                    url: route,
                    target: button.target,
                    options: button.specs,
                }
                buttons.push(<button type="button" key={name} title={button.title}
                                     className="button bg-gray-100 hover:text-green-500"
                                     onClick={() => this.functions.openUrl(url)}><span className={button.class}/>
                </button>)
            }
        })
        if (this.state.form.value !== '') {
            buttons.push(<button type="button" title={this.functions.translate('Erase Content')} key={'refresh'}
                                 className="button bg-gray-100 hover:text-yellow-500"
                                 onClick={() => this.clearAutoSuggest()}><span className={'fas fa-eraser fa-fw'}/>
            </button>)
        }
        return buttons
    }

    render() {
        return (
            <div id={this.state.form.id + '_auto_suggest'} className={ this.state.suggestionsClass }>
                <div className="z-10 rounded border border-solid border-gray-300 w-full">
                    <a data-toggle={this.state.form.id + '_auto_suggest'} className="float-right text-xs underline md:hidden text-gray-600"
                       href="#" onClick={() => this.toggleAutoSuggestList()}><span className={'far fa-times-circle fa-fw'} title={'Close'}/></a>
                    <div className="w-full sm:py-2">
                        <div {...this.wrapper_attr}>
                            <Autosuggest
                                id={this.state.form.id}
                                suggestions={this.state.suggestions}
                                onSuggestionsFetchRequested={this.autoSuggestMatches}
                                onSuggestionsClearRequested={this.resetSuggestMatches}
                                getSuggestionValue={this.getSuggestedValue}
                                renderSuggestion={this.renderSuggestion}
                                inputProps={{
                                    value: this.state.value,
                                    placeholder: this.state.form.placeholder,
                                    autoComplete: 'stop_all_stuff',
                                    onChange: this.onChange,
                                }}
                            />
                            <div className={'button-right'}>
                                {this.getButtons()}
                            </div>
                            {this.errors}
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

FormAutoSuggest.propTypes = {
    form: PropTypes.object.isRequired,
    wrapper_attr: PropTypes.object.isRequired,
    widget_attr: PropTypes.object.isRequired,
    errors: PropTypes.array,
    functions: PropTypes.object.isRequired,
}

FormAutoSuggest.defaultProps = {
    errors: [],
}