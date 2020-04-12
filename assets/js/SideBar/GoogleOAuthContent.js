'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function GoogleOAuthContent(props) {
    const {
        functions,
        login,
        state
    } = props

    if (!login.on)
        return ''

    const showHide = state.showGoogleOptions ? 'loginOptions flex flex-col sm:flex-row justify-between content-center p-0' : 'loginOptions flex flex-col sm:flex-row justify-between content-center p-0 hidden'

    const academicYearOptions = Object.keys(login.academicYears).map(name => {
        const id = login.academicYears[name]
        return (<option value={id} key={id}>{name}</option>)
    })

    const languageOptions = Object.keys(login.languages).map(name => {
        const id = login.languages[name]
        return (<option value={id} key={id}>{name}</option>)
    })

    let content = (
        <div className="column-no-break">
            <h3>{functions.translate('Login with Google')}</h3>
            <div style={{backgroundColor: '#edf7ff'}}>
                <form action="#" method="post" autoComplete="on" encType="multipart/form-data"
                      className="blank fullWidth loginTableGoogle" id="loginFormGoogle">
                    <table className="noIntBorder fullWidth loginTableGoogle relative" cellSpacing="0">
                        <tbody>
                            <tr className="flex flex-col sm:flex-row justify-between content-center p-0">
                                <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 fullWidth"
                                    colSpan="2">
                                    <button id="googleConnect" type={'button'} className="w-full bg-white rounded shadow border border-gray-400 flex items-center px-2 py-1 mb-2 text-gray-600 hover:shadow-md hover:border-blue-600 hover:text-blue-600" onClick={() => functions.googleOAuth()}>
                                        <img className="w-10 h-10" src={login.login_img} /><span className="flex-grow text-lg">{ functions.translate('Login with Google')}</span>
                                    </button>
                                </td>
                            </tr>
                            <tr className={showHide}>
                                <td className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 ">
                                    <span className="fa-calendar fas fa-fw text-gray-600" title={functions.translate('Academic Year')} style={{width:'20px',height:'20px',margin:'5px 0 0 2px', fontSize: '15px', display: 'block'}}/>
                                </td>
                                <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 fullWidth">
                                    <div className="flex-1 relative">
                                        <select id="googleAcademicYear" name="AcademicYear" className="fullWidth" defaultValue={login.academicYear}>
                                            {academicYearOptions}
                                        </select>
                                    </div>
                                </td>

                            </tr>
                            <tr className={showHide}>
                                <td className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 ">
                                    <span className="fas fa-globe fa-fw text-gray-600" style={{width:'20px', height:'20px', margin:'5px 0 0 2px',fontSize: '15px', display: 'block'}} title={functions.translate('Language')} />
                                </td>
                                <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 fullWidth">
                                    <div className="flex-1 relative">
                                        <select id="googleI18n" name="i18n" className="fullWidth" defaultValue={login.language}>
                                            {languageOptions}
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td className=" px-2 border-b-0 sm:border-b border-t-0 right" colSpan="2">
                                    <a className="showGoogleOptions" onClick={() => functions.showHideGoogle()}>{functions.translate('Options')}</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>

            </div>
        </div>
    )

    return content
}

GoogleOAuthContent.propTypes = {
    login: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    state: PropTypes.object.isRequired,
}