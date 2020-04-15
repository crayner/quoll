'use strict';

import React from 'react'
import { render } from 'react-dom'
import PaginationApp from "./Pagination/PaginationApp";

window.onload = function () {
    if (typeof window.PAGINATION_PROPS.contentList === 'object') {
        window.PAGINATION_PROPS.contentList.map(content => {
            const target = content.targetElement
            const paginationContent = document.getElementById(target)

            if (paginationContent === null)
                render(<div>&nbsp;</div>, document.getElementById('dumpStuff'))
            else
                render(
                    <PaginationApp
                        {...content}
                    />,
                    paginationContent
                )
        })
    } else {
        const target = window.PAGINATION_PROPS.targetElement
        const paginationContent = document.getElementById(target)

        if (paginationContent === null)
            render(<div>&nbsp;</div>, document.getElementById('dumpStuff'))
        else
            render(
                <PaginationApp
                    {...window.PAGINATION_PROPS}
                />,
                paginationContent
            )
    }
};
