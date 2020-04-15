'use strict'

import React, { Component } from 'react'
import PropTypes from 'prop-types'
import LoginContent from "./LoginContent"
import GoogleOAuthContent from "./GoogleOAuthContent"

export default class Login extends Component {
    constructor(props) {
        super(props)
        this.login = props.login
        this.googleOAuth = props.googleOAuth
        this.googleOAuth.academicYears = this.login.academicYears
        this.googleOAuth.academicYear = this.login.academicYear
        this.googleOAuth.languages = this.login.languages
        this.googleOAuth.language = this.login.language
        this.translations = props.translations

        this.state = {
            showLoginOptions: false,
            showGoogleOptions: false
        }

        this.functions = {
            translate: this.translate.bind(this),
            showHideLogin: this.showHideLogin.bind(this),
            showHideGoogle: this.showHideGoogle.bind(this),
            googleOAuth: this.googleOAuthLogin.bind(this),
        }
    }

    showHideLogin() {
        this.setState({
            showLoginOptions: !this.state.showLoginOptions
        })
    }

    showHideGoogle() {
        this.setState({
            showGoogleOptions: !this.state.showGoogleOptions
        })
    }

    googleOAuthLogin() {
        let url = this.googleOAuth.googleOAuthURL
        if (this.state.showGoogleOptions) {
            const googleAcademicYear = document.getElementById('googleAcademicYear').value
            const googleLanguage = document.getElementById('googleI18n').value
            url = url + '?state=' + googleAcademicYear + ':' + googleLanguage
        } else {
            url = url + '?state=0:0'
        }
        window.open(url,'_self')
    }

    translate(id) {
        if (typeof this.translations[id] === 'undefined') {
            console.error('No translations for ' + id)
            return id
        }
        return this.translations[id]
    }

    getLoginContent() {
        let content = []
        content.push(<GoogleOAuthContent login={this.googleOAuth} state={this.state} functions={this.functions} key={'google'} />)
        content.push(<LoginContent login={this.login} functions={this.functions} state={this.state} key={'login'} />)
        return content
    }

    render () {
        return (this.getLoginContent())
    }
}

Login.propTypes = {
    login: PropTypes.object.isRequired,
    googleOAuth: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
}


