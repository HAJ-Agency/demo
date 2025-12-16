import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

import './style.scss';
import './editor.scss';
const edit = (props) => {
   const { attributes, setAttributes, clientId } = props;
   const { blockId, mainClassName } = attributes;

   React.useEffect(() => {
      if (!blockId) {
         setAttributes({
            blockId: clientId,
         });
      }
      if (!mainClassName) {
         setAttributes({
            mainClassName: `search-header block-editor-block-list__block wp-block ${blockProps.className}`,
         });
      }
   }, []);

   const blockProps = useBlockProps();

   return (
      <Fragment>
         <InspectorControls></InspectorControls>
         <ServerSideRender
            block="tiburon/search-header"
            attributes={attributes}
         />
      </Fragment>
   );
};
export default edit;
