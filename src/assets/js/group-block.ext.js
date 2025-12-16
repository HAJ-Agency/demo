const { addFilter } = wp.hooks;
const { InspectorControls, BlockEdit, useSetting } = wp.blockEditor;
const { PanelBody, ToggleControl, TextControl, ColorPalette, ColorPicker, RangeControl } = wp.components;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { __ } = wp.i18n;

// Add new attributes for group link
const addExtraAttributes = (settings, name) => {
   if (name === "core/group") {
      settings.attributes = {
         ...settings.attributes,
         groupLink: {
            type: "string",
            default: "",
         },
         groupLinkOpenNewTab: {
            type: "boolean",
            default: false,
         },
         backgroundColor1: { type: "string", default: "" },
         backgroundColor2: { type: "string", default: "" },
         backgroundSplit: { type: "number", default: 50 },
      };
   }
   return settings;
};
addFilter("blocks.registerBlockType", "extend-core-group/extend-group-attributes", addExtraAttributes);

// Add controls to the Inspector panel for the group block
// For rendering - see filters.php
const withExtraControls = createHigherOrderComponent((BlockEdit) => {
   return (props) => {
      const { attributes, setAttributes, name } = props;

      if (name === "core/group") {
         const { groupLink, groupLinkOpenNewTab, backgroundColor1, backgroundColor2, backgroundSplit } = attributes;
         const colors = useSetting("color.palette");
         return (
            <Fragment>
               <InspectorControls group="settings">
                  <PanelBody title={__("Bakgrundsfärger (Gradient)", "tiburon")} initialOpen={backgroundColor1 && backgroundColor2}>
                     <ColorPalette
                        colors={colors}
                        label={__("Färg 1", "tiburon")}
                        value={backgroundColor1}
                        disableCustomColors={true}
                        onChange={(value) => setAttributes({ backgroundColor1: value })}
                     />
                     <ColorPalette
                        colors={colors}
                        label={__("Färg 2", "tiburon")}
                        value={backgroundColor2}
                        disableCustomColors={true}
                        onChange={(value) => setAttributes({ backgroundColor2: value })}
                     />
                     <RangeControl label={__("Dela vid (%)", "tiburon")} value={backgroundSplit} onChange={(value) => setAttributes({ backgroundSplit: value })} min={0} max={100} step={1} />
                  </PanelBody>
                  <PanelBody title={__("Länka Grupp-block", "tiburon")} initialOpen={groupLink !== ""}>
                     <TextControl label={__("Länk/URL", "tiburon")} value={groupLink} onChange={(value) => setAttributes({ groupLink: value })} placeholder="https://example.com" />
                     <ToggleControl
                        label={__("Öppna i ny flik", "tiburon")}
                        checked={groupLinkOpenNewTab}
                        onChange={() =>
                           setAttributes({
                              groupLinkOpenNewTab: !groupLinkOpenNewTab,
                           })
                        }
                     />
                  </PanelBody>
               </InspectorControls>
               <BlockEdit {...props} />
            </Fragment>
         );
      }

      return <BlockEdit {...props} />;
   };
}, "withExtraControls");
addFilter("editor.BlockEdit", "extend-core-group/with-hover-color-controls", withExtraControls);

// Frontend - Save CSS custom properties for responsive behavior
const addPropsToFrontend = (extraProps, blockType, attributes) => {
   if (blockType.name === "core/group") {
      const { backgroundColor1, backgroundColor2, backgroundSplit } = attributes;

      if (backgroundColor1 && backgroundColor2) {
         extraProps.style = {
            ...extraProps.style,
            "--bg-color-1": backgroundColor1,
            "--bg-color-2": backgroundColor2,
            "--bg-split": backgroundSplit + "%",
         };
         extraProps.className = [extraProps.className || "", "responsive-gradient"].join(" ");
      }
   }
   return extraProps;
};
addFilter("blocks.getSaveContent.extraProps", "extend-core-group/apply-styles", addPropsToFrontend);

// Editor
const addPropsToEditor = createHigherOrderComponent((BlockListBlock) => {
   return (props) => {
      const { name, attributes } = props;

      if (name === "core/group") {
         const { backgroundColor1, backgroundColor2, backgroundSplit } = attributes;
         let wrapperProps = {};

         if (backgroundColor1 && backgroundColor2) {
            wrapperProps.style = {
               ...wrapperProps.style,
               backgroundImage: `linear-gradient(90deg, ${backgroundColor1} ${backgroundSplit}%, ${backgroundColor2} ${backgroundSplit}%)`,
            };
         }

         return <BlockListBlock {...props} wrapperProps={wrapperProps} />;
      }

      return <BlockListBlock {...props} />;
   };
}, "addPropsToEditor");

addFilter("editor.BlockListBlock", "extend-core-group/apply-styles-editor", addPropsToEditor);
