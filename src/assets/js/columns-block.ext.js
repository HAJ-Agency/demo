const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls, BlockEdit, useSetting } = wp.blockEditor;
const { PanelRow, PanelBody, ToggleControl, TextControl, ColorPalette, RangeControl } =
   wp.components;
const { Fragment } = wp.element;
const { __ } = wp.i18n;

// Add extra attributes to the block
const addExtraAttributes = (settings, name) => {
   if (name === "core/columns") {
      settings.attributes = {
         ...settings.attributes,
         hasColumnDivider: { type: "boolean", default: false },
         columnDividerColor: { type: "string", default: "#000000" },
         columnDividerWidth: { type: "number", default: 1 },
      };
   }
   return settings;
};
addFilter(
   "blocks.registerBlockType",
   "extend-columns-block/add-extra-attributes",
   addExtraAttributes
);

// Add a input field to the block's Inspector Controls
const withExtraControls = createHigherOrderComponent((BlockEdit) => {
   return (props) => {
      const { attributes, setAttributes, name } = props;
      if (name === "core/columns") {
         const { hasColumnDivider, columnDividerColor, columnDividerWidth } = attributes;
         const colors = useSetting("color.palette");
         return (
            <Fragment>
               <BlockEdit {...props} />
               <InspectorControls>
                  <PanelBody title={__("Kolumnavdelare", "your-textdomain")}>
                     <ToggleControl
                        label={__("Visa avdelare mellan kolumner", "your-textdomain")}
                        checked={hasColumnDivider}
                        onChange={(value) => setAttributes({ hasColumnDivider: value })}
                     />
                     {hasColumnDivider && (
                        <>
                           <ColorPalette
                              colors={colors}
                              label={__("Färg", "your-textdomain")}
                              value={columnDividerColor}
                              disableCustomColors={true}
                              onChange={(value) => setAttributes({ columnDividerColor: value })}
                           />
                           <RangeControl
                              label={__("Tjocklek (px)", "your-textdomain")}
                              value={columnDividerWidth}
                              onChange={(value) => setAttributes({ columnDividerWidth: value })}
                              min={1}
                              max={10}
                           />
                        </>
                     )}
                  </PanelBody>
               </InspectorControls>
            </Fragment>
         );
      }

      return <BlockEdit {...props} />;
   };
}, "withExtraControls");
addFilter("editor.BlockEdit", "extend-core-columns/with-extra-controls", withExtraControls);

// Frontend
const addPropsToFrontend = (extraProps, blockType, attributes) => {
   if (blockType.name === "core/columns") {
      const { hasColumnDivider, columnDividerColor, columnDividerWidth } = attributes;

      if (hasColumnDivider) {
         extraProps.className = [extraProps.className || "", "has-column-divider"].join(" ");

         extraProps.style = {
            ...extraProps.style,
            "--column-divider-color": columnDividerColor,
            "--column-divider-width": `${columnDividerWidth}px`,
         };
      }
   }
   return extraProps;
};
addFilter("blocks.getSaveContent.extraProps", "extend-core-group/apply-styles", addPropsToFrontend);

// Editor
const addPropsToEditor = createHigherOrderComponent((BlockListBlock) => {
   return (props) => {
      const { name, attributes } = props;

      if (name === "core/columns") {
         const { hasColumnDivider, columnDividerColor, columnDividerWidth } = attributes;
         let wrapperProps = {};

         if (hasColumnDivider) {
            wrapperProps.className = [wrapperProps.className || "", "has-column-divider"].join(" ");

            let horizontalGap = "2rem"; // fallback
            const node = document.querySelector(`[data-block="${props.clientId}"]`);
            if (node) {
               const gapValue = getComputedStyle(node).gap;
               const parts = gapValue.split(" ");
               if (parts.length === 2) {
                  horizontalGap = parts[1]; // vertical gap [0], horizontal gap [1]
               }
            }

            wrapperProps.style = {
               ...wrapperProps.style,
               "--column-divider-color": columnDividerColor,
               "--column-divider-width": `${columnDividerWidth}px`,
               "--wp--columns-horizontal-gap": horizontalGap,
            };
         }

         return <BlockListBlock {...props} wrapperProps={wrapperProps} />;
      }

      return <BlockListBlock {...props} />;
   };
}, "addPropsToEditor");

addFilter("editor.BlockListBlock", "extend-core-group/apply-styles-editor", addPropsToEditor);
