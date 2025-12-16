import { __ } from "@wordpress/i18n";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { Fragment, useEffect } from "@wordpress/element";
import ServerSideRender from "@wordpress/server-side-render";

import "./style.scss";
import "./editor.scss";
const edit = (props) => {
   const { attributes, setAttributes, clientId, name } = props;
   const { blockId, mainClassName } = attributes;

   useEffect(() => {
      if (!blockId) {
         setAttributes({
            blockId: clientId,
         });
      }
      if (!mainClassName) {
         setAttributes({
            mainClassName: `tiburon-reseller-search-bar ${blockProps.className}`,
         });
      }
   }, []);

   const blockProps = useBlockProps();

   return (
      <Fragment>
         <InspectorControls></InspectorControls>
         <div {...blockProps}>
            <ServerSideRender block={name} attributes={attributes} />
         </div>
      </Fragment>
   );
};
export default edit;
