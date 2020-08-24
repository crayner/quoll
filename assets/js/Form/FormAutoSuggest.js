'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Autosuggest from 'react-autosuggest'

export default function FormAutoSuggest(props) {
    const {
        wrapper_attr,
        errors,
        functions,
        form
    } = props

    function onChange(event) {
        if (event.target.value !== undefined)
        {
            form.autoSuggestValue = event.target.value
            functions.mergeSubForm(form)
        }
    }

    function getButtons() {
        let buttons = []
        Object.keys(form.buttons).map(name => {
            const button = form.buttons[name]
            if (name === 'add' && form.value === '') {
                let url = {
                    url: button.route,
                    target: button.target,
                    options: button.specs,
                }
                buttons.push(<button type={'button'} key={name} title={button.title} className={'button bg-gray-100 hover:text-indigo-500'} onClick={() => functions.openUrl(url)}>
                    <span className={button.class}></span>
                </button>)
            }
            if (name === 'refresh' && form.value === '') {
                form.auto_refresh_url = button.route
                buttons.push(<button type={'button'} key={name} title={button.title} className={'button bg-gray-100 hover:text-indigo-500'} onClick={() => functions.refreshChoiceList({...form})}>
                    <span className={button.class}></span>
                </button>)
            }
            if (name === 'edit' && form.value !== '') {
                let route = button.route.replace("__value__", form.value)
                let url = {
                    url: route,
                    target: button.target,
                    options: button.specs,
                }
                buttons.push(<button type="button" key={name} title={button.title}
                                     className="button bg-gray-100 hover:text-green-500"
                                     onClick={() => functions.openUrl(url)}><span className={button.class}/>
                </button>)
            }
        })
        if (form.value !== '') {
            buttons.push(<button type="button" title={functions.translate('Erase Content')} key={'refresh'}
                                 className="button bg-gray-100 hover:text-yellow-500"
                                 onClick={() => clearAutoSuggest()}><span className={'fas fa-eraser fa-fw'}/>
            </button>)
        }
        return buttons
    }

    function renderSuggestion(suggestion) {
        return (<span>{suggestion.label}</span>)
    }

    function autoSuggestMatches(value) {
        value = value.value.trim().toLowerCase()
        if (value === '') {
            form.autoSuggestValue = ''
            return resetSuggestMatches()
        }
        const suggestions = form.choices.filter(choice => {
            const label = choice.label.toLowerCase()
            if (label.includes(value)) {
                return choice
            }
        })
        form.suggestions = suggestions
        form.autoSuggestValue = value
        functions.mergeSubForm(form)
    }

    function resetSuggestMatches() {
        form.suggestions = []
        form.autoSuggestValue = ''
        functions.mergeSubForm(form)
    }

    function getSuggestedValue(suggestion) {
        form.value = suggestion.value
        form.autoSuggestValue = suggestion.label
        form.suggestions = []
        functions.mergeSubForm(form)
    }

    function toggleAutoSuggestList()
    {
        let suggestionsClass = 'xs:block absolute xs:static top-0 right-0 w-full'
        if (form.suggestionsClass === suggestionsClass) {
            suggestionsClass = 'xs:block absolute xs:static top-0 right-0 w-full hidden'
        }
        form.suggestionsClass = suggestionsClass
        functions.mergeSubForm(form)
    }

    function clearAutoSuggest() {
        form.value = ''
        form.suggestions = []
        form.autoSuggestValue = ''
        functions.mergeSubForm(form)
    }

    function setAutoSuggestValue() {
        if (form.value !== '') {
            form.choices.map(choice => {
                if (choice.value === form.value) {
                    form.autoSuggestValue = choice.label
                }
            })
        }
    }

    setAutoSuggestValue()

    return (
        <div id={form.id + '_auto_suggest'} className={ form.suggestionsClass }>
            <div className="z-10 rounded border border-solid border-gray-300 w-full">
                <a data-toggle={form.id + '_auto_suggest'} className="float-right text-xs underline xs:hidden text-gray-600"
                   href="#" onClick={() => toggleAutoSuggestList()}><span className={'far fa-times-circle fa-fw'} title={'Close'}/></a>
                <div className="w-full sm:py-2">
                    <div {...wrapper_attr}>
                        <Autosuggest
                            id={form.id}
                            suggestions={form.suggestions}
                            onSuggestionsFetchRequested={autoSuggestMatches}
                            onSuggestionsClearRequested={resetSuggestMatches}
                            getSuggestionValue={getSuggestedValue}
                            renderSuggestion={renderSuggestion}
                            inputProps={{
                                value: form.autoSuggestValue,
                                placeholder: form.placeholder,
                                autoComplete: 'stop_all_stuff',
                                onChange: onChange,
                                className: 'w-full',
                            }}
                        />
                        <div className={'button-right'}>
                            {getButtons()}
                        </div>
                        {errors}
                    </div>
                </div>
            </div>
        </div>
    )
}

FormAutoSuggest.propTypes = {
    form: PropTypes.object.isRequired,
    wrapper_attr: PropTypes.object.isRequired,
    errors: PropTypes.array,
    functions: PropTypes.object.isRequired,
}

FormAutoSuggest.defaultProps = {
    errors: [],
}