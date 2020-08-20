'use strict'

import React from 'react'
import PropTypes from 'prop-types'
import Row from "./Template/Table/Row"
import Messages from "../component/Messages"
import PaginationApp from "../Pagination/PaginationApp"

export default function FormApp(props) {
    const {
        functions,
        form,
        formName,
        singleForm,
        visibleKeys,
        panelName
    } = props

    if (form.template === 'table') {
        let rows = []
        Object.keys(form.children).map(key => {
            const child = form.children[key]
            if (child.panel === false || child.panel === formName || child.panel === panelName)
                rows.push(<Row key={key} form={child} functions={functions} columns={form.columns} visibleKeys={visibleKeys}/>)
        })

        let columns = []
        for (let i = 0; i < columns; i++) {
            columns.push(<td key={i}/>)
        }
        let dummyRow = (<tr style={{display: 'none'}}>{columns}</tr>)

        let table_attr = {}
        table_attr.className = 'smallIntBorder fullWidth standardForm relative'
        if (form.row_class !== null) table_attr.className = form.row_class

        if (typeof form.attr === 'undefined') {
            form.attr = {}
        }
        if (typeof form.attr.class !== 'undefined') {
            form.attr.className = form.attr.class
            delete form.attr.class
        }

        if (singleForm) {
            return (<section className={'panelSection'}>
                <Messages messages={form.errors} translate={functions.translate} />
                <table {...table_attr}>
                    <tbody>
                    {dummyRow}
                    {rows}
                    </tbody>
                </table>
            </section>)
        }

        return (<section className={'panelForm'}>
                    <Messages messages={form.errors} translate={functions.translate} />
                    <form
                        action={form.action}
                        id={form.id}
                        {...form.attr}
                        method={form.method !== undefined ? form.method : 'POST'}>
                        <table {...table_attr}>
                            <tbody>
                                {dummyRow}
                                {rows}
                            </tbody>
                        </table>
                    </form>
                </section>
        )
    }
    // Future Expansion for grid not table
    return (null)
}

FormApp.propTypes = {
    form: PropTypes.object.isRequired,
    functions: PropTypes.object.isRequired,
    formName: PropTypes.string.isRequired,
    panelName: PropTypes.string.isRequired,
    singleForm: PropTypes.bool.isRequired,
    visibleKeys: PropTypes.object.isRequired,
}
