import React from 'react'
import PropTypes from 'prop-types'

const marginTabs = {
    margin: '0px'
};

const tabsBar = (props) => {
    return (
        <div className="tabs js-tabs">
            <div className='tabs__tablist' style={marginTabs} aria-label="tabnavigation">
                {renderTabs(props.tabs, props.onSelect, props.selected)}
            </div>
        </div>
    )
}

const renderTabs = (tabs, onSelect, selected) => {
    if (tabs.length > 0) {
        return tabs.map((tab, key) => {

            const onClick = ev => {
                ev.preventDefault()
                onSelect(tab)
            }

            return (
                <button key={key} type="button" className="tabs__tab " role="tab" id="" onClick={onClick} aria-selected={tab.component === selected}>{tab.name}</button>
            )
        })
    }
}

tabsBar.defaultProps = {
    tabs: []
}

tabsBar.propTypes = {
    tabs: PropTypes.array,
    onSelect: PropTypes.func.isRequired,
    selected: PropTypes.string
}

export default tabsBar
