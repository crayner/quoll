'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function PaginationSearch(props) {
    const {
        search,
        changeSearch,
        clearSearch,
        messages,
        searchValue,
    } = props

    if (! search)
        return (<tr style={{display: 'none'}}/>)


    return (<tr className={'flex flex-col sm:flex-row justify-between content-center p-0'}>
                <td className={'flex flex-col flex-grow justify-center -mb-1 sm:mb-0 px-2 border-b-0 sm:border-b border-t-0'}>
                    <label htmlFor="search_input">{messages['Search for']}</label>
                    <span id="manage_search_search_help" className="text-xxs text-gray-600 italic font-normal mt-1 sm:mt-0 help-text">{messages['Search in']}</span>
                </td>
                <td className={'w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0'}>
                    <div className={'flex-1 relative'}>
                        <input type={'text'} id={'search_input'} onChange={(e) => changeSearch(e)} value={searchValue} className={'w-full'} />
                        <div className={'button-right'}>
                            <button type={'button'} title={messages['Clear']} onClick={() => clearSearch()} className={'button'}><span className={'fa-fw fas fa-broom'}></span></button>
                        </div>
                    </div>
                </td>
            </tr>)
}

PaginationSearch.propTypes = {
    messages: PropTypes.object.isRequired,
    search: PropTypes.bool.isRequired,
    searchValue: PropTypes.string.isRequired,
    changeSearch: PropTypes.func.isRequired,
    clearSearch: PropTypes.func.isRequired,
}

