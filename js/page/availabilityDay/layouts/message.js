import React, { PropTypes } from 'react'

const Board = (props) => {
    const className = `message ${props.className}`

    return (
        <section className="block">
            <h2 className="block__heading">{props.title}</h2>
            <div className={className} role="alert">            
                {props.body}
            </div>
        </section>
    )
}

Board.propTypes = {
    className: PropTypes.string,
    title: PropTypes.node.isRequired,
    body: PropTypes.node.isRequired,
}

export default Board
