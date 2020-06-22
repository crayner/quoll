'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function Footer(props) {
    const {
        details
    } = props

    return (
        <div className="relative bg-transparent-600 text-white text-center text-sm p-6 mb-10 leading-normal rounded-b">
            <span className="inline-block">
                        {details.translations['Powered by']} <a className="link-white" target="_blank" href="http://www.craigrayner.com/kookaburra">{details.translations.Kookaburra}</a>&nbsp;</span>
            <span className="inline-block">|  © <a className="link-white" target="_blank" href="http://www.craigrayner.com">Craig Rayner</a> 2019-{details.year}</span>
            <br/>
            <span className="text-xs">
                {details.translations['Created under the']} <a className="link-white" target="_blank"
                                     href='https://opensource.org/licenses/MIT'>MIT</a> {details.translations['licence']},&nbsp;
                {details.translations['from a fork of']} <a className="link-white" target='_blank' href='https://gibbonedu.org'>Gibbon v18.0.01</a>
                    <br />
                {details.footerThemeAuthor}<br />
             </span>
            <img src={details.footerLogo} className={'absolute right-0 top-0 -mt-2 sm:mr-0 md:mr-12 opacity-75 hidden sm:block'} />
        </div>
    )
}

Footer.propTypes = {
    details: PropTypes.object.isRequired,
}
