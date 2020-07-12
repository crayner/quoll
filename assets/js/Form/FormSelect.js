'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function FormSelect(props) {
    const {
        form,
        wrapper_attr,
        widget_attr,
        errors,
        functions,
    } = props
console.log(form)
    var options = []
    if (typeof form.placeholder !== 'undefined' && form.placeholder !== false){
        options.push(<option key={'placeholder'} value='' className={'text-gray-500'}>{form.placeholder}</option>)
    }

    Object.keys(form.preferred_choices).map(choice => {
        if (typeof form.preferred_choices[choice].choices === 'undefined') {
            options.push(<option value={form.preferred_choices[choice].value}
                                 key={form.preferred_choices[choice].value}>{form.preferred_choices[choice].label}</option>)
            delete form.choices[choice]
        } else {
            const groupName = form.preferred_choices[choice].label
            let subOptions = []
            Object.keys(form.preferred_choices[choice].choices).map(subChoice => {
                subOptions.push(<option value={form.preferred_choices[choice].choices[subChoice].value}
                                        key={form.preferred_choices[choice].choices[subChoice].value}>{form.preferred_choices[choice].choices[subChoice].label}</option>)
            })
            options.push(<optgroup key={groupName} label={groupName}>{subOptions}</optgroup>)
        }
    })
    if (Object.keys(form.preferred_choices).length > 0) {
        options.push(<option key={'bars'}> ---</option>)
    }

    Object.keys(form.choices).map(choice => {
        if (typeof form.choices[choice].choices === 'undefined') {
            options.push(<option value={form.choices[choice].value}
                                 key={form.choices[choice].value}>{form.choices[choice].label}</option>)
        } else {
            const groupName = form.choices[choice].label
            let subOptions = []
            Object.keys(form.choices[choice].choices).map(subChoice => {
                subOptions.push(<option value={form.choices[choice].choices[subChoice].value}
                                        key={form.choices[choice].choices[subChoice].value}>{form.choices[choice].choices[subChoice].label}</option>)
            })
            options.push(<optgroup key={groupName} label={groupName}>{subOptions}</optgroup>)
        }
    })

    let buttons = []
    if (form.auto_refresh)  {
        buttons.push(<button type="button" title={functions.translate('Refresh List')} key={'refresh'}
                             className="button" onClick={() => functions.refreshChoiceList(form)}><span className={'fas fa-sync fa-fw'} /></button>)
        if (form.add_url !== null)
            buttons.push(<button title={functions.translate('Add Element to List')} key={'add'} onClick={(e) => functions.addElementToChoice(e,form.add_url)}
                                 className="button" style={{marginRight: '20px'}}><span className={'fas fa-plus fa-fw'} /></button>)
    }

    if (typeof form.value === 'undefined' || form.value === null) {
        form.value = ''
    }

    return (
        <div {...wrapper_attr}>
            <select multiple={form.multiple} {...widget_attr} value={form.value} data-value={form.value}>
                {options}
            </select>
            {buttons.length > 0 ?
                (<div className={'button-right'}>
                {buttons}
            </div>) : null}
            {errors}
        </div>
    )
}

FormSelect.propTypes = {
    form: PropTypes.object.isRequired,
    wrapper_attr: PropTypes.object.isRequired,
    widget_attr: PropTypes.object.isRequired,
    errors: PropTypes.array,
    functions: PropTypes.object.isRequired,
}

FormSelect.defaultProps = {
    errors: [],
}