import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, RangeControl, ToggleControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

import "./editor.scss";

const edit = (props) => {
   const { attributes, setAttributes, clientId } = props;
   const {
      blockId,
      mainClassName,
      numberOfProducts,
      postsPerViewDesktop,
      postsPerViewMobile,
      isGrid,
   } = attributes;

   const blockProps = useBlockProps(); // (define this before using it below)

   React.useEffect(() => {
      if (!blockId) {
         setAttributes({ blockId: clientId });
      }
      if (!mainClassName) {
         setAttributes({
            mainClassName: `filtered-products-section swiper block-editor-block-list__block wp-block ${blockProps.className}`,
         });
      }
   }, []);

   return (
      <>
         <InspectorControls>
            <PanelBody title="Layout">
               <ToggleControl
                  label="Visa som grid i stället för slider"
                  checked={!!isGrid}
                  onChange={(val) => setAttributes({ isGrid: val })}
                  help={isGrid ? "Grid-läge är aktivt." : "Slider-läge är aktivt."}
               />
            </PanelBody>

            <PanelBody title="Slider Inställningar">
               <RangeControl
                  label="Antal produkter (0 = alla)"
                  value={numberOfProducts}
                  onChange={(val) => setAttributes({ numberOfProducts: val })}
                  min={0}
                  max={36}
                  disabled={!!isGrid} // disable when grid is active
               />
               <RangeControl
                  label="Synliga Produkter - Dator"
                  value={postsPerViewDesktop}
                  onChange={(val) => setAttributes({ postsPerViewDesktop: val })}
                  min={1}
                  max={5}
                  disabled={!!isGrid} // disable when grid is active
               />
               <RangeControl
                  label="Synliga Produkter - Mobil"
                  value={postsPerViewMobile}
                  onChange={(val) => setAttributes({ postsPerViewMobile: val })}
                  min={1}
                  max={5}
                  disabled={!!isGrid} // disable when grid is active
               />
            </PanelBody>
         </InspectorControls>

         <ServerSideRender block="tiburon/filtered-products-section" attributes={attributes} />
      </>
   );
};

export default edit;
