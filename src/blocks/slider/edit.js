import { __ } from "@wordpress/i18n";
import { InnerBlocks, InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { Fragment, useCallback } from "@wordpress/element";

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
            mainClassName: `tiburon-slider ${blockProps.className}`,
         });
      }
   }, []);

   const blockProps = useBlockProps();

   return (
      <Fragment>
         <InspectorControls></InspectorControls>
         <div {...blockProps}>
            <div id={blockId} className={mainClassName}>
               <InnerBlocks
                  allowedBlocks={["tiburon/slider-item"]}
                  template={[["tiburon/slider-item", {}]]}
               />
            </div>
         </div>
      </Fragment>
   );
};
export default edit;
