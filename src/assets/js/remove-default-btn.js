wp.domReady(() => {
   // Unregister all core/button styles
   const buttonStyles = ['default', 'outline', 'fill'];

   buttonStyles.forEach((style) => {
      wp.blocks.unregisterBlockStyle('core/button', style);
   });
});
