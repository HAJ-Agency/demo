import { __ } from "@wordpress/i18n";
import { InnerBlocks, InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { Fragment } from "@wordpress/element";

import "./style.scss";
import "./editor.scss";
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
            mainClassName: `tiburon-slider-item ${blockProps.className}`,
         });
      }
   }, []);

   const blockProps = useBlockProps();

   return (
      <Fragment>
         <InspectorControls></InspectorControls>
         <div {...blockProps}>
            <div id={blockId} className={mainClassName}>
               <InnerBlocks template={[["core/paragraph", {}]]} />
            </div>
         </div>
      </Fragment>
   );
};
export default edit;
