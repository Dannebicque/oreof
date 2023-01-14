/**
 *
 * Scripts
 *
 * Initialization of the template base and page scripts.
 *
 *
 */

class Scripts {
  constructor() {
    this._initCommon();
    // this._initIcons();
    // this._initComponents();
    // this._initApps();
    // this._initPages();
    // this._initForms();
    // this._initPlugins();
  }

  // Common plugins and overrides initialization
  _initCommon() {
    // common.js initialization
    if (typeof Common !== 'undefined') {
      let common = new Common();
    }
  }

  // Base scripts initialization
  _initBase() {
    // Navigation
    if (typeof Nav !== 'undefined') {
      const nav = new Nav(document.getElementById('nav'));
    }

    // AcornIcons initialization
    if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
  }

  // Icon pages initialization
  _initIcons() {
    // interface.icons.js initialization
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
  }

  // Components pages initialization
  _initComponents() {
    // cards.js initialization
    if (typeof ComponentsCards !== 'undefined') {
      const componentsCards = new ComponentsCards();
    }

    // navs.js initialization
    if (typeof ComponentsNavs !== 'undefined') {
      const componentsNavs = new ComponentsNavs();
    }

    // progress.js initialization
    if (typeof ComponentsProgress !== 'undefined') {
      const componentsProgress = new ComponentsProgress();
    }

    // spinners.js initialization
    if (typeof ComponentsSpinners !== 'undefined') {
      const componentsSpinners = new ComponentsSpinners();
    }

    // toasts.js initialization
    if (typeof ComponentsToasts !== 'undefined') {
      const componentsToasts = new ComponentsToasts();
    }
  }

  // Application pages initialization
  _initApps() {
    // calendar.js initialization
    if (typeof Calendar !== 'undefined') {
      let calendar = new Calendar();
    }
    // mailbox.js initialization
    if (typeof Mailbox !== 'undefined') {
      let mailbox = new Mailbox();
    }
    // contacts.js initialization
    if (typeof Contacts !== 'undefined') {
      let contacts = new Contacts();
    }
    // chat.js initialization
    if (typeof Chat !== 'undefined') {
      const chat = new Chat();
    }
    // task.js initialization
    if (typeof Tasks !== 'undefined') {
      const tasks = new Tasks();
    }
  }

  // Form and form controls pages initialization
  _initForms() {
    // layouts.js initialization
    if (typeof FormLayouts !== 'undefined') {
      const formLayouts = new FormLayouts();
    }
    // validation.js initialization
    if (typeof FormValidation !== 'undefined') {
      const formValidation = new FormValidation();
    }
    // wizards.js initialization
    if (typeof FormWizards !== 'undefined') {
      const formWizards = new FormWizards();
    }
    // inputmask.js initialization
    if (typeof InputMask !== 'undefined') {
      const inputMask = new InputMask();
    }
    // controls.autocomplete.js initialization
    if (typeof GenericForms !== 'undefined') {
      const genericForms = new GenericForms();
    }
    // controls.autocomplete.js initialization
    if (typeof AutocompleteControls !== 'undefined') {
      const autocompleteControls = new AutocompleteControls();
    }
    // controls.datepicker.js initialization
    if (typeof DatePickerControls !== 'undefined') {
      const datePickerControls = new DatePickerControls();
    }
    // controls.datepicker.js initialization
    if (typeof DropzoneControls !== 'undefined') {
      const dropzoneControls = new DropzoneControls();
    }
    // controls.editor.js initialization
    if (typeof EditorControls !== 'undefined') {
      const editorControls = new EditorControls();
    }
    // controls.spinner.js initialization
    if (typeof SpinnerControls !== 'undefined') {
      const spinnerControls = new SpinnerControls();
    }
    // controls.rating.js initialization
    if (typeof RatingControls !== 'undefined') {
      const ratingControls = new RatingControls();
    }
    // controls.select2.js initialization
    if (typeof Select2Controls !== 'undefined') {
      const select2Controls = new Select2Controls();
    }
    // controls.slider.js initialization
    if (typeof SliderControls !== 'undefined') {
      const sliderControls = new SliderControls();
    }
    // controls.tag.js initialization
    if (typeof TagControls !== 'undefined') {
      const tagControls = new TagControls();
    }
    // controls.timepicker.js initialization
    if (typeof TimePickerControls !== 'undefined') {
      const timePickerControls = new TimePickerControls();
    }
  }

  // Plugin pages initialization
  _initPlugins() {
    // carousels.js initialization
    if (typeof Carousels !== 'undefined') {
      const carousels = new Carousels();
    }
    // charts.js initialization
    if (typeof Charts !== 'undefined') {
      const charts = new Charts();
    }
    // contextmenu.js initialization
    if (typeof ContextMenu !== 'undefined') {
      const contextMenu = new ContextMenu();
    }
    // lightbox.js initialization
    if (typeof Lightbox !== 'undefined') {
      const lightbox = new Lightbox();
    }

    // lists.js initialization
    if (typeof Lists !== 'undefined') {
      const lists = new Lists();
    }
    // notifies.js initialization
    if (typeof Notifies !== 'undefined') {
      const notifies = new Notifies();
    }
    // players.js initialization
    if (typeof Players !== 'undefined') {
      const players = new Players();
    }
    // progressbars.js initialization
    if (typeof ProgressBars !== 'undefined') {
      const progressBars = new ProgressBars();
    }
    // shortcuts.js initialization
    if (typeof Shortcuts !== 'undefined') {
      const shortcuts = new Shortcuts();
    }
    // sortables.js initialization
    if (typeof Sortables !== 'undefined') {
      const sortables = new Sortables();
    }
    // datatable.editablerows.js initialization
    if (typeof EditableRows !== 'undefined') {
      const editableRows = new EditableRows();
    }
    // datatable.editableboxed.js initialization
    if (typeof EditableBoxed !== 'undefined') {
      const editableBoxed = new EditableBoxed();
    }
    // datatable.ajax.js initialization
    if (typeof RowsAjax !== 'undefined') {
      const rowsAjax = new RowsAjax();
    }
    // datatable.serverside.js initialization
    if (typeof RowsServerSide !== 'undefined') {
      const rowsServerSide = new RowsServerSide();
    }
    // datatable.serverside.js initialization
    if (typeof BoxedVariations !== 'undefined') {
      const boxedVariations = new BoxedVariations();
    }
  }

  // Pages initialization
  _initPages() {
    // dashboard.default.js initialization
    if (typeof DashboardDefault !== 'undefined') {
      let dashboardDefault = new DashboardDefault();
    }
    // dashboard.analytic.js initialization
    if (typeof DashboardAnalytic !== 'undefined') {
      let dashboardAnalytic = new DashboardAnalytic();
    }
    // dashboard.visual.js initialization
    if (typeof DashboardVisual !== 'undefined') {
      let dashboardVisual = new DashboardVisual();
    }
    // auth.login.js initialization
    if (typeof AuthLogin !== 'undefined') {
      const authLogin = new AuthLogin();
    }
    // auth.register.js initialization
    if (typeof AuthRegister !== 'undefined') {
      const authRegister = new AuthRegister();
    }
    // auth.forgotpassword.js initialization
    if (typeof AuthForgotPassword !== 'undefined') {
      const authForgotPassword = new AuthForgotPassword();
    }
    // auth.resetpassword.js initialization
    if (typeof AuthResetPassword !== 'undefined') {
      const authResetPassword = new AuthResetPassword();
    }
    // blocks.details.js initialization
    if (typeof BlocksDetails !== 'undefined') {
      const blocksDetails = new BlocksDetails();
    }
    // blocks.gallery.js initialization
    if (typeof BlocksGallery !== 'undefined') {
      const blocksGallery = new BlocksGallery();
    }
    // blocks.list.js initialization
    if (typeof BlocksList !== 'undefined') {
      const blocksList = new BlocksList();
    }
    // blocks.stats.js initialization
    if (typeof BlocksStats !== 'undefined') {
      const blocksStats = new BlocksStats();
    }
    // blocks.tabulardata.js initialization
    if (typeof BlocksTabularData !== 'undefined') {
      const blocksTabularData = new BlocksTabularData();
    }
    // blocks.thumbnails.js initialization
    if (typeof BlocksThumbnails !== 'undefined') {
      const blocksThumbnails = new BlocksThumbnails();
    }
    // blocks.users.js initialization
    if (typeof BlocksUsers !== 'undefined') {
      const blocksUsers = new BlocksUsers();
    }
    // blog.home.js initialization
    if (typeof BlogHome !== 'undefined') {
      const blogHome = new BlogHome();
    }
    // blog.detail.js initialization
    if (typeof BlogDetail !== 'undefined') {
      const blogDetail = new BlogDetail();
    }
    // misc.comingsoon.js initialization
    if (typeof ComingSoon !== 'undefined') {
      const comingSoon = new ComingSoon();
    }
    // portfolio.detail.js initialization
    if (typeof PortfolioDetail !== 'undefined') {
      const portfolioDetail = new PortfolioDetail();
    }
    // portfolio.home.js initialization
    if (typeof PortfolioHome !== 'undefined') {
      const portfolioHome = new PortfolioHome();
    }
    // profile.settings.js initialization
    if (typeof ProfileSettings !== 'undefined') {
      const profileSettings = new ProfileSettings();
    }
    // profile.standard.js initialization
    if (typeof ProfileStandard !== 'undefined') {
      const profileStandard = new ProfileStandard();
    }
  }

  // Settings initialization
  _initSettings() {
    if (typeof Settings !== 'undefined') {
      const settings = new Settings({attributes: {placement: 'horizontal'}, showSettings: true, storagePrefix: 'acorn-classic-dashboard-'});
    }
  }

  // Variables initialization of Globals.js file which contains valus from css
  _initVariables() {
    if (typeof Variables !== 'undefined') {
      const variables = new Variables();
    }
  }

  // Listeners of menu and layout changes which fires a resize event
  _addListeners() {
    document.documentElement.addEventListener(Globals.menuPlacementChange, (event) => {
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 25);
    });

    document.documentElement.addEventListener(Globals.layoutChange, (event) => {
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 25);
    });

    document.documentElement.addEventListener(Globals.menuBehaviourChange, (event) => {
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 25);
    });
  }
}

// Shows the template after initialization of the settings, nav, variables and common plugins.
(function () {
  window.addEventListener('DOMContentLoaded', () => {
    // Initializing of the Scripts
    if (typeof Scripts !== 'undefined') {
      const scripts = new Scripts();
    }
  });
})();

// Disabling dropzone auto discover before DOMContentLoaded
(function () {
  if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false;
  }
})();
