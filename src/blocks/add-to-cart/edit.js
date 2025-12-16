import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl, RangeControl } from "@wordpress/components";
import { useSelect } from "@wordpress/data";
import ServerSideRender from "@wordpress/server-side-render";

import "./editor.scss";
const edit = (props) => {
   const { attributes, setAttributes, clientId } = props;
   const { blockId, mainClassName, numberOfProducts, postsPerViewDesktop, postsPerViewMobile } =
      attributes;
   React.useEffect(() => {
      if (!blockId) {
         setAttributes({
            blockId: clientId,
         });
      }
      if (!mainClassName) {
         setAttributes({
            mainClassName: `tiburon-add-to-cart swiper block-editor-block-list__block wp-block ${blockProps.className} `,
         });
      }
   }, []);
   const blockProps = useBlockProps();

   return (
      <>
         <InspectorControls></InspectorControls>

         <ServerSideRender block="tiburon/add-to-cart" attributes={attributes} />
      </>
   );
};

export default edit;
