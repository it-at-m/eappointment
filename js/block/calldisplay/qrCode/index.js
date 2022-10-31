import React, { Component } from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import qrGenerator from "qrcode-generator"

class QrCodeView extends Component {
    constructor(props) {
        super(props)

        window.setTimeout(() => {
            this.initQrCode(props.targetUrl)
        }, 1000)
    }

    initQrCode(targetUrl) {
        let frameDocument = $('iframe.qrCodeView')[0].contentWindow.document
        let frameHead = frameDocument.getElementsByTagName('head')[0]
        let frameBody = frameDocument.getElementsByTagName('body')[0]

        let linkElement = frameDocument.createElement('link')
        linkElement.type = 'text/css'
        linkElement.rel = 'stylesheet'
        linkElement.href = '/admin/_css/admin.css'

        frameHead.appendChild(linkElement)

        let styleElement = frameDocument.createElement('style')
        styleElement.appendChild(frameDocument.createTextNode(
            'html { overflow: hidden; }'
        ))
        frameHead.appendChild(styleElement);

        let qrCodeCanvas = frameDocument.createElement('canvas')
        qrCodeCanvas.className = 'qrCode'
        qrCodeCanvas.width = '400'
        qrCodeCanvas.height = '400'
        qrCodeCanvas.style = 'position: absolute; top: 46px; left: 4px;'

        let printButton = frameDocument.createElement('button')
        printButton.style = 'position: absolute; top: 10px; left: 10px; height:24px; min-height: 0; padding:2px;'
        printButton.className = 'button no-print'
        printButton.innerHTML = 'Drucken'
        printButton.onclick = (event) => {
            event.preventDefault()
            $('iframe.qrCodeView')[0].contentWindow.print()
        }

        let closeButton = frameDocument.createElement('button')
        closeButton.style = 'position: absolute; top: 10px; right: 10px; width: 24px; height:24px; min-height: 0; padding:2px;'
        closeButton.className = 'button button--destructive no-print'
        closeButton.innerHTML = '&#10005;'
        closeButton.onclick = (event) => {
            event.preventDefault()
            this.props.togglePopup()
        }

        frameBody.append(printButton);
        frameBody.append(closeButton);
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
        };
        return (
            <div className="lightbox">
                <div className="lightbox__content" role="dialog" aria-modal="true">
                    <iframe className="qrCodeView" width="405" height="450" style={styles} />
                </div>
            </div>
        )
    }
}

QrCodeView.propTypes = {
    targetUrl: PropTypes.string,
}

export default QrCodeView