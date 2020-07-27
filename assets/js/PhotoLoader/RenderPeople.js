'use strict'

import React from "react"
import PropTypes from 'prop-types'
import Dropzone, { formatBytes } from 'react-dropzone-uploader'
import 'react-dropzone-uploader/dist/styles.css'
import FormAutoSuggest from '../Form/FormAutoSuggest'
import Autosuggest from 'react-autosuggest'

export default function RenderPeople(props) {
    const {
        people,
        chosen,
        addMessage,
        replacePerson,
        validateImage,
        removePhoto,
        messages,
        absolute_url,
        functions,
        suggestions,
        autoSuggestValue,
    } = props

    const SingleFileAutoSubmit = () => {

        const getUploadParams = () => {
            let url = absolute_url + '/personal/photo/{person}/upload/'
            url = url.replace('{person}', chosen.value)
            return {url: url}
        }

        const handleChangeStatus = ({meta, remove, xhr}, status) => {
            if (status === 'aborted') {
                addMessage(messages['aborted'].replace('{name}', meta.name), 'error')
            } else if (status === 'done') {
                let data = JSON.parse(xhr.response)
                addMessage(data['message'],data['status'])
                remove()
                replacePerson(data.person)
            } else if (status === 'error_file_size') {
                let message = messages['error_size'].replace('{size}', formatBytes(meta.size))
                addMessage(message ,'error')
                remove()
            } else if (status === 'error_upload') {
                addMessage(messages['aborted'].replace('{name}', meta.name), 'error')
                remove()
            } else if (status === 'error_validation') {
                let message = ''
                if (meta.height > 960 || meta.width > 720 )
                    message = messages['error_height_width'].replace('{height}', meta.height).replace('{width}', meta.width)
                if (meta.height < 320 || meta.width < 240)
                    message = messages['error_height_width_minimum'].replace('{height}', meta.height).replace('{width}', meta.width)
                const ratio = meta.width / meta.height
                if ((ratio < 0.7 || ratio > 0.84) && message === '')
                    message = messages['error_ratio'].replace('{ratio}', ratio.toFixed(2))
                addMessage(message ,'error')
                remove()
            }
        }

        const dropFilesHere = (meta) => {
            return (<div style={{width: '100%', height: '100%', textAlign: 'center', verticalAlign: 'middle', position: 'relative'}}>
                <span style={{position: 'absolute', margin: 0, top: '50%', left: '50%', transform: 'translate(-50%, -50%)', fontSize: '0.8rem'}}>{meta.extra.reject ? messages['Images [.jpg, .png, .jpeg, .gif] only'] : messages['Drop Image Here']}</span>
            </div>)
        }

        return (
            <Dropzone
                getUploadParams={getUploadParams}
                onChangeStatus={handleChangeStatus}
                maxFiles={1}
                multiple={false}
                canCancel={false}
                maxSizeBytes={350000}
                validate={validateImage}
                acceptedFiles="image/jpeg,image/png,image/jpg,image/gif"
                InputComponent={dropFilesHere}
                styles={{
                    dropzone: {height: 120, borderColor: 'gray', borderStyle: 'solid', borderWidth: '1px'},
                    dropzoneActive: {borderColor: 'green', backgroundColor: 'aquamarine'},
                    dropzoneReject: {borderColor: 'red', backgroundColor: 'moccasin'},
                }}
                classNames={{
                    previewImage: 'user max100',
                }}
            />
        )
    }

    function getForm() {
        return {
            choices: people,
            buttons: {},
            suggestions: [],
        }
    }

    function getWidgetAttr() {
        return {}
    }

    function getWrapperAttr() {
        return {}
    }

    function renderSuggestion(suggestion) {
        return (<span>{suggestion.label}</span>)
    }


    function getAutoSuggest() {
        return <Autosuggest
            id='photo.loader'
            suggestions={suggestions}
            onSuggestionsFetchRequested={functions.autoSuggestMatches}
            onSuggestionsClearRequested={functions.resetSuggestMatches}
            getSuggestionValue={functions.getSuggestedValue}
            renderSuggestion={renderSuggestion}
            inputProps={{
                value: autoSuggestValue,
                placeholder: messages.placeholder,
                autoComplete: 'stop_all_stuff',
                onChange: functions.onChange,
            }}
        />

    }

    function getLabel(chosen) {
        return chosen.label.replace(chosen.type + ': ', '')
    }

    function getPhoto(chosen) {
        if (chosen.photo.includes(absolute_url) && (chosen.photo[0] === '/' || chosen.photo[0] === '\\')) {
            return chosen.photo.slice(1)
        }
        if (chosen.photo.length > 1 && (chosen.photo[0] === '/' || chosen.photo[0] === '\\')) {
            return chosen.photo
        }
        if (chosen.photo.includes(absolute_url)) {
            return chosen.photo
        }
        return '/' + chosen.photo

    }

    return (
        <table className="noIntBorder fullWidth relative">
            <tbody>
                <tr className="flex flex-col sm:flex-row justify-between content-center p-0">
                    <td className="flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0 ">
                        <label htmlFor={'people_drop'} className={'inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs'}>{messages['Target Person']}<br/><span className={'text-xs text-gray-600 italic font-normal mt-1 sm:mt-0'}>{messages['target_person_help']}</span></label>
                    </td>
                    <td className="w-full max-w-full sm:max-w-xs flex justify-end items-center px-2 border-b-0 sm:border-b border-t-0 ">
                        <div className="flex-1 relative">
                            {getAutoSuggest()}
                        </div>
                    </td>
                </tr>
                {typeof chosen.value === 'string' ?
                <tr className="flex flex-col sm:flex-row justify-between content-center p-0">
                    <td className="flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0 ">
                        <label className={'inline-block mt-4 sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs'}>{messages['Replace this image']}<br/><span style={{fontWeight: 'normal'}}>{getLabel(chosen)}</span>
                            <button type={'button'} className={'close-button grey'} title={messages['Remove Photo']} onClick={() => removePhoto(chosen)} style={{float: 'right', marginTop: '-19px'}} >
                                <span className={'fas fa-eraser fa-fw'} />
                            </button>
                            <img src={getPhoto(chosen)} title={getLabel(chosen)} className={'user max100 right'} style={{float: 'right', marginTop: '-19px'}} />
                        </label>
                    </td>
                    <td className="flex-grow justify-center px-2 border-b-0 sm:border-b border-t-0 right">
                        <SingleFileAutoSubmit />
                    </td>
                </tr> : null}
            </tbody>
        </table>
    )
}

RenderPeople.propTypes = {
    people: PropTypes.array.isRequired,
    chosen: PropTypes.object.isRequired,
    addMessage: PropTypes.func.isRequired,
    validateImage: PropTypes.func.isRequired,
    replacePerson: PropTypes.func.isRequired,
    removePhoto: PropTypes.func.isRequired,
    messages: PropTypes.object.isRequired,
    absolute_url: PropTypes.string.isRequired,
    functions: PropTypes.object.isRequired,
    suggestions: PropTypes.array.isRequired,
    autoSuggestValue: PropTypes.string.isRequired,
}
