'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function PaginationFilter(props) {
    const {
        changeFilter,
        filters,
        filter,
        filterGroups,
        messages,
        defaultFilters
    } = props

    if (Object.keys(filters).length === 0)
        return (<tr style={{display: 'none'}}/>)

    let optionGroups = []
    Object.keys(filters).map(name => {
        let value = filters[name]
        if (!optionGroups.includes(value.group))
            optionGroups.push(value.group)
    })
    let filterOptions = []
    optionGroups.map(group => {
        let options = Object.keys(filters).filter(name => {
            let value = filters[name]
            if (group === value.group)
                return value
        })
        const choices = options.map(name => {
            let value = filters[name]
            return (<option value={value.name} key={name}>{value.label}</option>)
        })
        filterOptions.push(<optgroup key={group} label={group}>{choices}</optgroup> )
    })
    filterOptions.unshift(<option value={''} key={0}>{messages['Filter']}</option>)

    let activeFilters = []
    if (filterGroups !== {}) {
        activeFilters = Object.keys(filterGroups).map(q => {
            const name = filterGroups[q]
            const value = filters[name]
            return (<span onClick={() => changeFilter(value)} className={'primary button-like pointer-hover ml-1'} key={q}>{name}&nbsp;<span className={'far fa-times-circle fa-fw'}></span></span>)
        })
    }

    return (<tr className={'flex flex-col sm:flex-row justify-between content-center p-0'}>
                <td className="flex flex-col flex-grow justify-center -mb-1 sm:mb-0 px-2 border-b-0 sm:border-b border-t-0">
                    <label htmlFor="manage_search_search">{messages['Filter Select']}{defaultFilters ? <span className={'text-xxs text-gray-600 italic font-normal mt-1 sm:mt-0'}><br />{messages['Default filtering is enforced.']}</span>: ''}</label>
                    <div style={{marginTop: '7px', height: '20px'}}>
                    {activeFilters}
                    </div>
                </td>
                <td className={'w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0'}>
                    <div className={'flex-1 relative'}>
                        <select id={'filter_select'} onChange={(e) => changeFilter(e)} value={''} className={'w-full'}>{filterOptions}</select>
                    </div>
                </td>
            </tr>)
}

PaginationFilter.propTypes = {
    filter: PropTypes.array.isRequired,
    filters: PropTypes.oneOfType([
        PropTypes.array,
        PropTypes.object,
    ]).isRequired,
    changeFilter: PropTypes.func.isRequired,
    messages: PropTypes.object.isRequired,
    filterGroups: PropTypes.object.isRequired,
    defaultFilters: PropTypes.bool.isRequired,
}

