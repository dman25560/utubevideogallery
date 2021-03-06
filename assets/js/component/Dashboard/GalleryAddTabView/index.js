import React from 'react';
import actions from './actions';
import utility from '../../shared/utility';
import Card from '../../shared/Card';
import Columns from '../../shared/Columns';
import Column from '../../shared/Column';
import SectionHeader from '../../shared/SectionHeader';
import Breadcrumbs from '../../shared/Breadcrumbs';
import Form from '../../shared/Form';
import FormField from '../../shared/FormField';
import Label from '../../shared/Label';
import TextInput from '../../shared/TextInput';
import SelectBox from '../../shared/SelectBox';
import SubmitButton from '../../shared/SubmitButton';
import CancelButton from '../../shared/CancelButton';

class GalleryAddTabView extends React.Component
{
  constructor(props)
  {
    super(props);

    this.state = {
      title: '',
      albumSorting: 'asc',
      thumbnailType: 'rectangle',
      displayType: 'album'
    };
  }

  changeValue = (e) =>
  {
    this.setState({[e.target.name]: e.target.value});
  }

  addGallery = async() =>
  {
    const rsp = await actions.createGallery(this.state);

    if (utility.isValidResponse(rsp))
    {
      this.props.changeView();
      this.props.setFeedbackMessage(utvJSData.localization.feedbackGalleryCreated);
    }
    else if (utility.isErrorResponse(rsp))
      this.props.setFeedbackMessage(utility.getErrorMessage(rsp), 'error');
  }

  render()
  {
    return (
      <div>
        <Breadcrumbs
          crumbs={[
            {text: utvJSData.localization.galleries, onClick: () => this.props.changeView()}
          ]}
        />
        <Columns>
          <Column className="utv-left-fixed-single-column">
            <Card>
              <SectionHeader text={utvJSData.localization.addGallery}/>
              <Form
                submit={this.addGallery}
                errorclass="utv-invalid-feedback"
              >
                <FormField>
                  <Label text={utvJSData.localization.title}/>
                  <TextInput
                    name="title"
                    value={this.state.title}
                    onChange={this.changeValue}
                    required={true}
                  />
                </FormField>
                <FormField>
                  <Label text={utvJSData.localization.albumSorting}/>
                  <SelectBox
                    name="albumSorting"
                    value={this.state.albumSorting}
                    onChange={this.changeValue}
                    data={[
                      {name: utvJSData.localization.ascending, value: 'asc'},
                      {name: utvJSData.localization.descending, value: 'desc'}
                    ]}
                    required={true}
                  />
                </FormField>
                <FormField>
                  <Label text={utvJSData.localization.thumbnailType}/>
                  <SelectBox
                    name="thumbnailType"
                    value={this.state.thumbnailType}
                    onChange={this.changeValue}
                    data={[
                      {name: utvJSData.localization.rectangle, value: 'rectangle'},
                      {name: utvJSData.localization.square, value: 'square'}
                    ]}
                    required={true}
                  />
                </FormField>
                <FormField>
                  <Label text={utvJSData.localization.displayType}/>
                  <SelectBox
                    name="displayType"
                    value={this.state.displayType}
                    onChange={this.changeValue}
                    data={[
                      {name: utvJSData.localization.albums, value: 'album'},
                      {name: utvJSData.localization.justVideos, value: 'video'}
                    ]}
                    required={true}
                  />
                </FormField>
                <FormField classes="utv-formfield-action">
                  <SubmitButton
                    title={utvJSData.localization.addGallery}
                  />
                  <CancelButton
                    title={utvJSData.localization.cancel}
                    onClick={() => this.props.changeView()}
                  />
                </FormField>
              </Form>
            </Card>
          </Column>
        </Columns>
      </div>
    );
  }
}

export default GalleryAddTabView;
