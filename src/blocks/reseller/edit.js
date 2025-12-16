import { __ } from "@wordpress/i18n";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { PanelBody, ToggleControl, PanelRow } from '@wordpress/components';
import { Fragment, useEffect } from "@wordpress/element";
import ServerSideRender from "@wordpress/server-side-render";

import "./style.scss";
import "./editor.scss";
const edit = (props) => {
   const { attributes, setAttributes, clientId, name } = props;
   const { blockId, mainClassName, hasSeparateSearchBar } = attributes;

   useEffect(() => {
      if (!blockId) {
         setAttributes({
            blockId: clientId,
         });
      }
      if (!mainClassName) {
         setAttributes({
            mainClassName: `tiburon-reseller ${blockProps.className}`,
         });
      }
   }, []);

   const blockProps = useBlockProps();

   return (
      <Fragment>
         <InspectorControls>
            <PanelBody title={__("Reseller Inställningar", "tiburon")}>
               <PanelRow>
                  <ToggleControl
                     label={__("Separat Sökfält", "tiburon")}
                     checked={hasSeparateSearchBar}
                     onChange={(value) => {
                        setAttributes({ hasSeparateSearchBar: value });
                     }}
                  />
               </PanelRow>
            </PanelBody>
         </InspectorControls>
         <div {...blockProps}>
            <ServerSideRender block={name} attributes={attributes} />
         </div>
      </Fragment>
   );
};
export default edit;
