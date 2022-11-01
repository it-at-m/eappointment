import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import qrGenerator from "qrcode-generator"
import FocusTrap from 'focus-trap-react'

class QrCodeView extends Component {
    constructor(props) {
        super(props)
        this.ref = React.createRef();
        this.handleTogglePopup = this.handleTogglePopup.bind(this);
        this.handleClickOutside = this.handleClickOutside.bind(this);
        this.keyDownHandler = this.keyDownHandler.bind(this);
    }

    componentDidMount() {
        this.initQrCode(this.props.targetUrl)
        document.addEventListener("keydown", this.keyDownHandler, true);
        document.addEventListener("mousedown", this.handleClickOutside);
    }

    componentWillUnmount() {
        document.removeEventListener("keydown", this.keyDownHandler, true);
        document.removeEventListener("mousedown", this.handleClickOutside);
    }

    handleClickOutside(event) {
        if (this.ref && !this.ref.current.contains(event.target)) {
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

    initQrCode(targetUrl) {
        let frameDocument = $('iframe.qrCodeView')[0].contentWindow.document
        let frameHead = frameDocument.getElementsByTagName('head')[0]
        let frameBody = frameDocument.getElementsByTagName('body')[0]

        let styleElement = frameDocument.createElement('style')
        styleElement.appendChild(frameDocument.createTextNode(
            'html { overflow: hidden; }'
        ))
        frameHead.appendChild(styleElement);

        let qrCodeCanvas = frameDocument.createElement('canvas')
        qrCodeCanvas.className = 'qrCode'
        qrCodeCanvas.width = '400'
        qrCodeCanvas.height = '400'

        frameBody.append(qrCodeCanvas);

        let options = {
            version: 10,
            ecLevel: 'M',
            fillColor: '#000',
            backgroundColor: '#fff',
            quiet: 5,
            size: 400,
            text: targetUrl
        }

        this.drawQrCode(qrCodeCanvas, options)
    }

    drawQrCode(canvas, options) {
        qrGenerator.stringToBytes = qrGenerator.stringToBytesFuncs['UTF-8']
        const codeObj = qrGenerator(options.version, options.ecLevel)
        codeObj.addData(options.text)
        codeObj.make()

        let ctx = canvas.getContext('2d')
        ctx.fillStyle = options.backgroundColor
        ctx.fillRect(0, 0, options.size, options.size)

        const gridSize  = codeObj.getModuleCount()
        const blockSize = options.size / (gridSize + 2 * options.quiet)
        const offset    = options.quiet * blockSize

        ctx.beginPath()
        ctx.fillStyle = options.fillColor
        for (let row = 0; row < gridSize; row += 1) {
            for (let col = 0; col < gridSize; col += 1) {
                let top  = offset + row * blockSize
                let left = offset + col * blockSize

                if (codeObj.isDark(row, col)) {
                    ctx.rect(left, top, blockSize, blockSize)
                }
            }
        }

        ctx.fill()
    }

    render() {
        const styles = {
            border: '0px',
            marginLeft: '-8px'
        };
        const headerstyles = {
            backgroundColor: '#cccccc', 
            padding: '5px',
            marginBottom: '-8px'
        };
        return (
            <div className="lightbox">
                <FocusTrap>
                    <div className="lightbox__content trap" role="dialog" aria-modal="true" ref={this.ref}>
                        <div className="form-actions" style={headerstyles}>
                            <button className="button no-print" onClick={(event) => {event.preventDefault(); $('iframe.qrCodeView')[0].contentWindow.print()}}>Drucken</button>
                            <button className="button button--destructive no-print" onClick={(event) => {this.handleTogglePopup(event);}}>X</button>
                        </div>
                        <iframe className="qrCodeView" width="400" height="400" style={styles} />
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