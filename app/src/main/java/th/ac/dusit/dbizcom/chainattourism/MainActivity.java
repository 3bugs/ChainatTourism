package th.ac.dusit.dbizcom.chainattourism;

import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;

import com.bumptech.glide.request.RequestOptions;
import com.glide.slider.library.Animations.DescriptionAnimation;
import com.glide.slider.library.SliderLayout;
import com.glide.slider.library.SliderTypes.BaseSliderView;
import com.glide.slider.library.SliderTypes.TextSliderView;
import com.glide.slider.library.Tricks.ViewPagerEx;

import java.util.ArrayList;

public class MainActivity extends AppCompatActivity implements BaseSliderView.OnSliderClickListener, ViewPagerEx.OnPageChangeListener {

    private SliderLayout mSlider;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        mSlider = findViewById(R.id.slider);

        ArrayList<String> listUrl = new ArrayList<>();
        //ArrayList<String> listName = new ArrayList<>();

        listUrl.add("http://5911011802058.msci.dusit.ac.th/chainat_tourism/images/hero01.png");
        //listName.add("JPG - Github");

        listUrl.add("http://5911011802058.msci.dusit.ac.th/chainat_tourism/images/hero02.png");
        //listName.add("PNG - Android Studio");

        RequestOptions requestOptions = new RequestOptions();
        requestOptions
                .centerCrop();
        //.diskCacheStrategy(DiskCacheStrategy.NONE)
        //.placeholder(R.drawable.placeholder)
        //.error(R.drawable.placeholder);

        for (int i = 0; i < listUrl.size(); i++) {
            TextSliderView sliderView = new TextSliderView(this);
            // if you want show image only / without description text use DefaultSliderView instead

            // initialize SliderLayout
            sliderView
                    .image(listUrl.get(i))
                    //.description(listName.get(i))
                    .setRequestOption(requestOptions)
                    //.setBackgroundColor(Color.WHITE)
                    .setProgressBarVisible(true)
                    .setOnSliderClickListener(this);

            //add your extra information
            sliderView.bundle(new Bundle());
            //sliderView.getBundle().putString("extra", listName.get(i));
            mSlider.addSlider(sliderView);
        }

        // set Slider Transition Animation
        // mSlider.setPresetTransformer(SliderLayout.Transformer.Default);
        mSlider.setPresetTransformer(SliderLayout.Transformer.Accordion);

        mSlider.setPresetIndicator(SliderLayout.PresetIndicators.Center_Bottom);
        mSlider.setCustomAnimation(new DescriptionAnimation());
        mSlider.setDuration(3000);
        mSlider.addOnPageChangeListener(this);
    }

    @Override
    protected void onStop() {
        mSlider.stopAutoCycle();
        super.onStop();
    }

    @Override
    public void onSliderClick(BaseSliderView slider) {

    }

    @Override
    public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

    }

    @Override
    public void onPageSelected(int position) {

    }

    @Override
    public void onPageScrollStateChanged(int state) {

    }
}
