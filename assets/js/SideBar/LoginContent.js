'use strict'

import React from "react"
import PropTypes from 'prop-types'

export default function LoginContent(props) {
    const {
        functions,
        login,
        state
    } = props

    const showHide = state.showLoginOptions ? 'loginOptions flex flex-col sm:flex-row justify-between content-center p-0' : 'loginOptions flex flex-col sm:flex-row justify-between content-center p-0 hidden'

    const academicYearOptions = Object.keys(login.academicYears).map(name => {
        const id = login.academicYears[name]
        return (<option value={id} key={id}>{name}</option>)
    })

    const languageOptions = Object.keys(login.languages).map(name => {
        const id = login.languages[name]
        return (<option value={id} key={id}>{name}</option>)
    })

    let content = (<div className={'column-no-break'}>
        <h3>{functions.translate('Login')}</h3>
        <form action={'/login/'} method={"post"} autoComplete={"off"} encType={"multipart/form-data"} id={"loginForm"} className={"noIntBorder fullWidth"} >
            <table className="noIntBorder fullWidth relative">
                <tbody>
                <tr className=" flex flex-col sm:flex-row justify-between content-center p-0">
                    <td className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 ">
                        <span className="fas fa-users fa-fw text-gray-600"
                              style={{width: '20px',height:'20px',margin:'5px 0 0 2px', fontSize: '15px', display: 'block'}}
                              title={functions.translate('Username or email')} />
                    </td>
                    <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 fullWidth">
                        <div className="flex-1 relative">
                            <input type="text" id="username" name="username" className="fullWidth" maxLength="50"
                                   placeholder={functions.translate('Username or email')} autoComplete="off"
                                   style={{cursor: 'auto'}} />
                        </div>
                    </td>
                </tr>
                <tr className=" flex flex-col sm:flex-row justify-between content-center p-0">
                    <td className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 ">
                        <span className="fas fa-fw fa-user-lock text-gray-600"
                              style={{width:'20px',height:'20px',margin:'5px 0 0 2px',fontSize: '15px', display: 'block'}}
                              title={functions.translate('Password')}/>
                    </td>
                    <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 fullWidth">
                        <div className="flex-1 relative">
                            <input type="password" id="password" name="password" className="fullWidth" maxLength="30"
                                   placeholder={functions.translate('Password')} autoComplete="off" style={{cursor: 'auto'}}/>
                        </div>
                    </td>
                </tr>
                <tr className={showHide}>
                    <td className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 ">
                        <span className="fa-calendar fas fa-fw text-gray-600" title={functions.translate('Academic Year')}
                              style={{width:'20px',height:'20px',margin:'5px 0 0 2px', fontSize: '15px', display: 'block'}}/>
                    </td>
                    <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 fullWidth">
                        <div className="flex-1 relative">
                            <select id="AcademicYear" name="AcademicYear" className="fullWidth" defaultValue={login.academicYear}>
                                {academicYearOptions}
                            </select>
                        </div>
                    </td>
                </tr>
                <tr className={showHide}>
                    <td className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 ">
                        <span className="fas fa-globe fa-fw text-gray-600" title={functions.translate('Language')}
                              style={{width:'20px', height:'20px', margin:'5px 0 0 2px',fontSize: '15px', display: 'block'}}/>
                    </td>
                    <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 fullWidth">
                        <div className="flex-1 relative">
                            <select id="i18n" name="i18n" className="fullWidth" defaultValue={login.language}>
                                {languageOptions}
                            </select>
                        </div>
                    </td>
                </tr>
                <tr className=" flex flex-col sm:flex-row justify-between content-center p-0">
                    <td className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 right" colSpan="2">
                        <a className="show_hide" onClick={() => functions.showHideLogin()} href="#">{functions.translate('Options')}</a> . <a
                            href={login.resetPasswordURL}>{functions.translate('Forgot Password')}?</a>
                    </td>
                </tr>
                <tr className=" flex flex-col sm:flex-row justify-between content-center p-0">
                    <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 right"
                        colSpan="2">
                        <input type="submit" value="Login"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>)

    return content
}

LoginContent.propTypes = {
    login: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    state: PropTypes.object.isRequired,
}