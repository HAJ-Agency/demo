const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const {
   PanelRow,
   PanelBody,
   ToggleControl,
   CustomSelectControl,
   RadioControl,
   TextControl,
   RangeControl,
} = wp.components;
const { Fragment, useRef } = wp.element;
const { __ } = wp.i18n;

// Add extra attributes to the block
const addExtraAttributes = (settings, name) => {
   if (name === "core/image") {
      settings.attributes = {
         ...settings.attributes,
         watermark: {
            type: "boolean",
            default: false,
         },
         watermarkPosition: {
            type: "object",
            default: {
               x: "0",
               y: "0",
            },
         },
         watermarkOpacity: {
            type: "number",
            default: 1,
         },
         isHideOnMobile: {
            type: "boolean",
            default: false,
         },
         isHideOnDesktop: {
            type: "boolean",
            default: false,
         },
      };
   }
   return settings;
};
addFilter(
   "blocks.registerBlockType",
   "extend-cover-block/add-extra-attributes",
   addExtraAttributes
);

// Add a input field to the block's Inspector Controls
const withExtraControls = createHigherOrderComponent((BlockEdit) => {
   return (props) => {
      const { attributes, setAttributes, name } = props;
      if (name === "core/image") {
         const { watermark, watermarkPosition, watermarkOpacity, isHideOnMobile, isHideOnDesktop } =
            attributes;

         const WatermarkPositionPicker = ({ value, onChange }) => {
            const pickerRef = useRef(null);

            const handleClick = (event) => {
               const boundingRect = pickerRef.current.getBoundingClientRect();
               const offsetX = event.clientX - boundingRect.left;
               const offsetY = event.clientY - boundingRect.top;

               const x = Math.round((offsetX / boundingRect.width) * 200 - 100);
               const y = Math.round((offsetY / boundingRect.height) * 200 - 100);

               onChange({
                  x: Math.max(-100, Math.min(100, x)),
                  y: Math.max(-100, Math.min(100, y)),
               });
            };

            return (
               <div
                  ref={pickerRef}
                  style={{
                     position: "relative",
                     width: "100%",
                     height: "150px",
                     background: "#f0f0f0",
                     border: "1px solid #ccc",
                     borderRadius: "4px",
                     cursor: "crosshair",
                     userSelect: "none",
                     boxSizing: "border-box",
                  }}
                  onClick={handleClick}>
                  {/* Marker for current position */}
                  <div
                     style={{
                        position: "absolute",
                        top: `${75 + (value.y * 75) / 100}px`, // Adjust for rectangle height
                        left: `${50 + (value.x * 50) / 100}%`, // Adjust for full width
                        width: "10px",
                        height: "10px",
                        background: "#D6EBFF",
                        border: "1px solid #002A3A",
                        borderRadius: "50%",
                        transform: "translate(-50%, -50%)",
                        pointerEvents: "none",
                     }}
                  />
               </div>
            );
         };

         const updateWatermarkPosition = (newPosition) => {
            setAttributes({ watermarkPosition: newPosition });
         };

         return (
            <Fragment>
               <BlockEdit {...props} />
               <InspectorControls>
                  <PanelBody title={__("Vattenstämpel", "trelleborg")}>
                     <PanelRow>
                        <div className="full-width-control">
                           <ToggleControl
                              label={__("Använd som vattenstämpel", "trelleborg")}
                              checked={watermark}
                              onChange={(value) => {
                                 setAttributes({ watermark: value });
                              }}
                           />
                        </div>
                     </PanelRow>
                     {watermark && (
                        <>
                           <PanelRow>
                              <WatermarkPositionPicker
                                 value={watermarkPosition}
                                 onChange={updateWatermarkPosition}
                              />
                           </PanelRow>
                           <PanelRow>
                              <div className="full-width-control">
                                 <RangeControl
                                    label={__("Opacitet", "trelleborg")}
                                    value={watermarkOpacity}
                                    onChange={(value) => setAttributes({ watermarkOpacity: value })}
                                    min={0}
                                    max={1}
                                    step={0.005}
                                 />
                              </div>
                           </PanelRow>
                        </>
                     )}
                  </PanelBody>
                  <PanelBody title={__("Dölj på olika skärmstorlekar", "trelleborg")}>
                     <PanelRow>
                        <div className="full-width-control">
                           <ToggleControl
                              label={__("Dölj på Mobil", "trelleborg")}
                              checked={isHideOnMobile}
                              onChange={(value) => {
                                 setAttributes({ isHideOnMobile: value });
                              }}
                           />
                        </div>
                     </PanelRow>
                     <PanelRow>
                        <div className="full-width-control">
                           <ToggleControl
                              label={__("Dölj på Desktop", "trelleborg")}
                              checked={isHideOnDesktop}
                              onChange={(value) => {
                                 setAttributes({ isHideOnDesktop: value });
                              }}
                           />
                        </div>
                     </PanelRow>
                  </PanelBody>
               </InspectorControls>
            </Fragment>
         );
      }
      return <BlockEdit {...props} />;
   };
}, "withExtraControls");
addFilter("editor.BlockEdit", "extend-core-image/with-extra-controls", withExtraControls);

// Frontend
const addPropsToFrontend = (extraProps, blockType, attributes) => {
   if (blockType.name === "core/image") {
      const { watermark, watermarkPosition, watermarkOpacity, isHideOnMobile, isHideOnDesktop } =
         attributes;

      if (watermark) {
         extraProps.style = {
            ...extraProps.style,
            "--watermark-position-x": `${watermarkPosition.x}`,
            "--watermark-position-y": `${watermarkPosition.y}`,
            "--watermark-opacity": watermarkOpacity !== undefined ? `${watermarkOpacity}` : 1,
         };

         extraProps.className = [extraProps.className || "", "image-is-watermark"]
            .filter(Boolean) // Remove empty strings
            .join(" ");
      }

      if (isHideOnMobile) {
         extraProps.className = [extraProps.className || "", "is-mobile-hidden"]
            .filter(Boolean) // Remove empty strings
            .join(" ");
      }

      if (isHideOnDesktop) {
         extraProps.className = [extraProps.className || "", "is-desktop-hidden"]
            .filter(Boolean) // Remove empty strings
            .join(" ");
      }
   }

   return extraProps;
};
wp.hooks.addFilter(
   "blocks.getSaveContent.extraProps",
   "extend-core-image/apply-styles",
   addPropsToFrontend
);

// Editor
const addPropsToEditor = createHigherOrderComponent((BlockListBlock) => {
   return (props) => {
      const { name, attributes } = props;

      if (name === "core/image") {
         const { watermark, watermarkPosition, watermarkOpacity, isHideOnMobile, isHideOnDesktop } =
            attributes;

         let wrapperProps = {};

         if (watermark) {
            wrapperProps["data-is-watermark"] = `true`;
            wrapperProps.style = {
               ...wrapperProps.style,
               "--watermark-position-x": `${watermarkPosition.x}`,
               "--watermark-position-y": `${watermarkPosition.y}`,
               "--watermark-opacity": watermarkOpacity !== undefined ? `${watermarkOpacity}` : 1,
            };
         }

         if (isHideOnMobile) {
            wrapperProps.className = [wrapperProps.className || "", "is-mobile-hidden"]
               .filter(Boolean) // Remove empty strings
               .join(" ");
         }

         if (isHideOnDesktop) {
            wrapperProps.className = [wrapperProps.className || "", "is-desktop-hidden"]
               .filter(Boolean) // Remove empty strings
               .join(" ");
         }

         return <BlockListBlock {...props} wrapperProps={wrapperProps} />;
      }

      return <BlockListBlock {...props} />;
   };
}, "addPropsToEditor");

wp.hooks.addFilter(
   "editor.BlockListBlock",
   "extend-core-image/apply-styles-editor",
   addPropsToEditor
);
