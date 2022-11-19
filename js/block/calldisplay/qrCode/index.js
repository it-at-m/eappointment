import React, { Component } from 'react'
import ReactToPrint, { PrintContextConsumer } from 'react-to-print';
import PropTypes from 'prop-types'
import FocusTrap from 'focus-trap-react'
import QrCodeComponent from './qrcode'

class QrCodeView extends Component {
    constructor(props) {
        super(props)
        this.lightBoxRef = React.createRef();
        this.handleTogglePopup = this.handleTogglePopup.bind(this);
        this.handleClickOutside = this.handleClickOutside.bind(this);
        this.keyDownHandler = this.keyDownHandler.bind(this);
    }

    componentDidMount() {
        document.addEventListener("keydown", this.keyDownHandler, true);
        document.addEventListener("mousedown", this.handleClickOutside);
    }

    componentWillUnmount() {
        document.removeEventListener("keydown", this.keyDownHandler, true);
        document.removeEventListener("mousedown", this.handleClickOutside);
    }

    handleClickOutside(event) {
        if (this.lightBoxRef && !this.lightBoxRef.current.contains(event.target)) {
            this.handleTogglePopup(event);
        }
    }

    handleTogglePopup(event) {
        event.preventDefault()
        this.props.togglePopup()
    }

    keyDownHandler(event) {
        if (event.key === "Escape") {
            this.handleTogglePopup(event);
        }
    }

    render() {
        const headerstyles = {
            backgroundColor: '#cccccc',
            padding: '5px'
        };
        return (
            <div className="lightbox">
                <FocusTrap>
                    <div className="lightbox__content trap" role="dialog" aria-modal="true" ref={this.lightBoxRef}>
                        <div className="qrCodeView">
                            <div className="form-actions no-print" style={headerstyles}>
                                <ReactToPrint content={() => this.qrCodeRef}>
                                    <PrintContextConsumer>
                                        {({ handlePrint }) => (
                                        <button 
                                            className="button" 
                                            onClick={(event) => { event.preventDefault(); handlePrint(); }}>Drucken</button>
                                        )}
                                    </PrintContextConsumer>
                                </ReactToPrint>
                                <button className="button button--destructive" onClick={(event) => { this.handleTogglePopup(event); }}>X</button>
                            </div>
                            <style>
                                {`@media print {
                                    .printContainer {
                                        background-color: #ffffff;
                                        display:flex;
                                        justify-content:center;
                                        align-items:center;
                                        height:100%;
                                    }
                                    html, body{
                                        height:100%;
                                        width:100%;
                                        }
                                    }
                                }`}
                            </style>
                            <div className="printContainer" ref={el => (this.qrCodeRef = el)}>
                                <QrCodeComponent width="400" height="400" className="qrCode" { ... {'targetUrl' : this.props.targetUrl} } />
                            </div>
                        </div>
                    </div>
                </FocusTrap>
            </div>
        )
    }
}

QrCodeView.propTypes = {
    targetUrl: PropTypes.string,
    togglePopup: PropTypes.func
}

export default QrCodeView