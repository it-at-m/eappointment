import React, { PropTypes } from 'react'

const titleAside = (title) => {
    if (title) {
        return (<div className="aside">{title}</div>)
    }
}

const headerRight = (header) => {
    if (header) {
        return (<div className="header_right">{header}</div>)
    }
}

const Board = (props) => {
    const className = `board ${props.className}`

    return (
        <div className={className}>
            <div className="header">
                <h2 className="title">{props.title}</h2>
                {titleAside(props.titleAside)}
                {headerRight(props.headerRight)}
            </div>
            <div className="body">
                {props.body}
            </div>
            <div className="footer">
                {props.footer}
            </div>
        </div>
    )
}

Board.propTypes = {
    className: PropTypes.string,
    title: PropTypes.node.isRequired,
    titleAside: PropTypes.node,
    headerRight: PropTypes.node,
    body: PropTypes.node.isRequired,
    footer: PropTypes.node.isRequired
}

export default Board
