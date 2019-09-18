import React, { Component, PropTypes } from 'react'
import * as Inputs from '../../lib/inputs'

class MandantView extends Component {
    constructor(props) {
        super(props)
    }

    componentDidUpdate() {
        //console.log("updated mandant component")
    }

    render() {
        const onChange = (field, value) => {
            if (field == 'source') {
                value = value.substring(0,10);
            }
            this.props.changeHandler(field, value)
        }

        return (
            <div>   
                <div className="fieldset panel--heavy">
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labelsmandant.label}
                            attributes={{"htmlFor":"mandantLabel"}}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="label"
                                value={(this.props.source) ? this.props.source.label : ''}
                                onChange={onChange}
                                attributes={{"id":"mandantLabel"}}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                    <Inputs.FormGroup>
                        <Inputs.Label
                            children={this.props.labelsmandant.source}
                            attributes={{"htmlFor":"mandantSource"}}
                        />
                        <Inputs.Controls>
                            <Inputs.Text
                                name="source"
                                value={(this.props.source) ? this.props.source.source : ''}
                                attributes={ {readOnly: this.props.source.lastChange, maxLength: 10, "id":"mandantSource", "aria-describedby":"help_mandantSource"} }
                                onChange={onChange}
                            />
                            <Inputs.Description
                                children={this.props.descriptions.mandantlabel}
                                attributes={{"id":"help_mandantSource"}}
                            />
                        </Inputs.Controls>
                    </Inputs.FormGroup>
                </div>
                <fieldset>
                    <legend>{this.props.labelsmandant.contact}</legend>
                    <div className="panel--heavy">
                        <Inputs.FormGroup>
                            <Inputs.Label
                                children={this.props.labelsmandant.name}
                                attributes={{"htmlFor":"mandantContact"}}
                            />
                            <Inputs.Controls>
                                <Inputs.Text
                                    name="contact[name]"
                                    value={(this.props.source.contact) ? this.props.source.contact.name : ''}
                                    onChange={onChange}
                                    attributes={{"id":"mandantContact"}}
                                />
                            </Inputs.Controls>
                        </Inputs.FormGroup>
                        <Inputs.FormGroup>
                            <Inputs.Label
                                children={this.props.labelsmandant.email}
                                attributes={{"htmlFor":"mandantEmail"}}
                            />
                            <Inputs.Controls>
                                <Inputs.Text
                                    name="contact[email]"
                                    value={(this.props.source.contact) ? this.props.source.contact.email : ''}
                                    onChange={onChange}
                                    attributes={{"id":"mandantEmail"}}
                                />
                            </Inputs.Controls>
                        </Inputs.FormGroup>
                    </div>
                </fieldset>
            </div>
        )
    }
}

MandantView.propTypes = {
    labelsmandant: PropTypes.array.isRequired,
    source: PropTypes.array.isRequired,
    changeHandler: PropTypes.handler,
    descriptions: PropTypes.array
}

export default MandantView
