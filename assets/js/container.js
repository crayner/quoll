'use strict';

import React from 'react'
import { render } from 'react-dom'
import ContainerApp from "./Container/ContainerApp"
import Applications from './Container/ContainerApplications'

if (window.CONTAINER_PROPS !== 'undefined') {
    const containers = window.CONTAINER_PROPS

    for (const key in containers)
    {
        var container = containers[key]

        var target = document.getElementById(container.target)
        if (container.application !== null && Applications[container.application] !== undefined && target !== null) {
            const ContainerApplication = Applications[container.application];
            render(<ContainerApplication
                content={container.content}
                panels={container.panels}
                selectedPanel={container.selectedPanel}
                translations={container.translations}
                actionRoute={container.actionRoute}
                forms={container.forms}
                extras={container.extras}
                showSubmitButton={container.showSubmitButton}
                contentLoader={container.contentLoader}
                hideSingleFormWarning={container.hideSingleFormWarning}
            />, target)
        } else if (target !== null) {
            render(
                <ContainerApp
                    content={container.content}
                    panels={container.panels}
                    selectedPanel={container.selectedPanel}
                    actionRoute={container.actionRoute}
                    translations={container.translations}
                    forms={container.forms}
                    showSubmitButton={container.showSubmitButton}
                    contentLoader={container.contentLoader}
                    hideSingleFormWarning={container.hideSingleFormWarning}
                />,
                target
            )
        }
    }
}