const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls } = wp.blockEditor;
const { PanelRow, PanelBody, ToggleControl, TextControl } = wp.components;
const { Fragment } = wp.element;
const { __ } = wp.i18n;

// Add extra attributes to the block
const addExtraAttributes = (settings, name) => {
   if (name === "core/column") {
      settings.attributes = {
         ...settings.attributes,
         isOrderResponsive: {
            type: "boolean",
            default: false,
         },
         orderMobileIndex: {
            type: "string",
            default: "0",
         },
      };
   }
   return settings;
};
addFilter("blocks.registerBlockType", "extend-column-block/add-extra-attributes", addExtraAttributes);

// Add a input field to the block's Inspector Controls
const withExtraControls = createHigherOrderComponent((BlockEdit) => {
   return (props) => {
      const { attributes, setAttributes, name } = props;
      if (name === "core/column") {
         const { isOrderResponsive, orderMobileIndex } = attributes;

         return (
            <Fragment>
               <BlockEdit {...props} />
               <InspectorControls>
                  <PanelBody title={__("Responsiv Ordning", "tiburon")}>
                     <PanelRow>
                        <div className="full-width-control">
                           <ToggleControl
                              label={__("Använd Responsiv Ordning", "tiburon")}
                              checked={isOrderResponsive}
                              onChange={(value) => {
                                 setAttributes({ isOrderResponsive: value });
                              }}
                           />
                        </div>
                     </PanelRow>
                     {isOrderResponsive && (
                        <PanelRow>
                           <TextControl
                              label={__("Index för kolumnens ordning på mobil", "tiburon")}
                              type="number"
                              value={parseInt(orderMobileIndex)}
                              onChange={(value) => {
                                 setAttributes({
                                    orderMobileIndex: value.toString(),
                                 });
                              }}
                           />
                        </PanelRow>
                     )}
                  </PanelBody>
               </InspectorControls>
            </Fragment>
         );
      }

      return <BlockEdit {...props} />;
   };
}, "withExtraControls");
addFilter("editor.BlockEdit", "extend-core-column/with-extra-controls", withExtraControls);

// Modify the block save function
const addClassesToSave = (extraProps, blockType, attributes) => {
   const { isOrderResponsive } = attributes;
   if (blockType.name === "core/column" && isOrderResponsive) {
      // Add is-responsive-order class to the block if isOffset is true
      extraProps.className = `${extraProps.className} is-responsive-order`;
   }
   return extraProps;
};
addFilter("blocks.getSaveContent.extraProps", "extend-core-column/add-classes-to-save", addClassesToSave);

// Apply the styles
const applyStylesToSave = (extraProps, blockType, attributes) => {
   if (blockType.name === "core/column") {
      const { isOrderResponsive, orderMobileIndex } = attributes;
      if (isOrderResponsive) {
         extraProps.style = {
            ...extraProps.style,
            "--order-index": orderMobileIndex,
         };
      }
   }

   return extraProps;
};
addFilter("blocks.getSaveContent.extraProps", "extend-core-column/apply-styles", applyStylesToSave);

// Apply in the editor
const addDataPropsInEditor = createHigherOrderComponent((BlockListBlock) => {
   return (props) => {
      const { name, attributes } = props;

      if (name === "core/column") {
         const { isOrderResponsive, orderMobileIndex } = attributes;
         if (isOrderResponsive) {
            return (
               <Fragment>
                  <BlockListBlock
                     {...props}
                     wrapperProps={{
                        style: {
                           "--order-index": orderMobileIndex,
                        },
                        "data-is-responsive-order": `true`,
                     }}
                  />
               </Fragment>
            );
         }
      }

      return <BlockListBlock {...props} />;
   };
}, "addDataPropsInEditor");
addFilter("editor.BlockListBlock", "extend-core-column/apply-styles-editor", addDataPropsInEditor);
